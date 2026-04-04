<?php

use App\Models\User;
use App\Services\BillingService;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Payment;
use Stripe\Exception\ApiConnectionException;
use Stripe\PaymentIntent;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('allows upgrade from pro to team', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('swapPlan')
        ->once()
        ->withArgs(fn ($u, $price) => $price === 'price_team_monthly')
        ->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertRedirect(route('billing.index', ['checkout' => 'success', 'plan' => 'team', 'swapped' => 'true']));
    $response->assertSessionHas('success');
});

it('allows upgrade from team to enterprise', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createTeamSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('swapPlan')->once()->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_enterprise_monthly',
    ]);

    $response->assertRedirect(route('billing.index', ['checkout' => 'success', 'plan' => 'enterprise', 'swapped' => 'true']));
});

it('allows downgrade from enterprise to team', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createEnterpriseSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('swapPlan')->once()->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertRedirect(route('billing.index', ['checkout' => 'success', 'plan' => 'team', 'swapped' => 'true']));
});

it('allows downgrade from team to pro', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createTeamSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('swapPlan')->once()->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_pro_monthly',
    ]);

    $response->assertRedirect(route('billing.index', ['checkout' => 'success', 'plan' => 'pro', 'swapped' => 'true']));
});

it('allows swap from monthly to annual within same tier', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('swapPlan')
        ->once()
        ->withArgs(fn ($u, $price) => $price === 'price_pro_annual')
        ->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_pro_annual',
    ]);

    $response->assertRedirect(route('billing.index', ['checkout' => 'success', 'plan' => 'pro', 'swapped' => 'true']));
});

it('validates price_id is required for swap', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $response = $this->actingAs($user)->post('/billing/swap', []);

    $response->assertSessionHasErrors('price_id');
});

it('requires active subscription to swap plans', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertSessionHas('error');
});

it('prevents swap when subscription expired', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertSessionHas('error');
});

it('rejects arbitrary price_id on swap', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_hacked_cheap',
    ]);

    $response->assertSessionHasErrors('price_id');
});

it('handles IncompletePayment on swap requiring SCA', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $paymentIntent = PaymentIntent::constructFrom(['id' => 'pi_swap_sca', 'status' => 'requires_action']);
    $payment = new Payment($paymentIntent);
    $mock->shouldReceive('swapPlan')
        ->once()
        ->andThrow(new IncompletePayment($payment));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertRedirect(route('cashier.payment', ['id' => 'pi_swap_sca']));
});

it('handles Stripe API error during swap', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('swapPlan')
        ->once()
        ->andThrow(new ApiConnectionException('Stripe API unavailable'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Unable to process your request. Please try again or contact support.');
});

it('forwards valid coupon to swapPlan', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('validateCouponCode')->with('SAVE10')->andReturnNull();
    $mock->shouldReceive('swapPlan')
        ->once()
        ->withArgs(fn ($u, $price, $coupon) => $price === 'price_team_monthly' && $coupon === 'SAVE10')
        ->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
        'coupon' => 'SAVE10',
    ]);

    $response->assertRedirect(route('billing.index', ['checkout' => 'success', 'plan' => 'team', 'swapped' => 'true']));
});

it('rejects coupon with invalid characters on swap', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
        'coupon' => 'INVALID COUPON!@#',
    ]);

    $response->assertSessionHasErrors('coupon');
});

it('rejects swap when Stripe coupon validation fails', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('validateCouponCode')->with('EXPIRED50')->andReturn('The coupon code is invalid or has expired.');
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
        'coupon' => 'EXPIRED50',
    ]);

    $response->assertSessionHasErrors('coupon');
});

// swap preview tests

it('returns proration preview for subscribed user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('previewSwapProration')
        ->once()
        ->withArgs(fn ($u, $price) => $price === 'price_team_monthly')
        ->andReturn(['amount_due' => 1500, 'next_billing_date' => '2026-05-01T00:00:00+0000']);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->getJson('/billing/swap/preview?price_id=price_team_monthly');

    $response->assertOk()
        ->assertJsonStructure(['amount_due', 'next_billing_date'])
        ->assertJsonPath('amount_due', 1500);
});

it('returns 500 when Stripe proration preview fails', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('previewSwapProration')
        ->once()
        ->andThrow(new ApiConnectionException('Stripe unavailable'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->getJson('/billing/swap/preview?price_id=price_team_monthly');

    $response->assertStatus(500);
});

it('returns 400 for swap preview when user has no subscription', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->getJson('/billing/swap/preview?price_id=price_team_monthly');

    $response->assertStatus(400);
});

it('returns 422 for swap preview when price_id is missing', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $response = $this->actingAs($user)->getJson('/billing/swap/preview');

    $response->assertStatus(422);
});

it('returns 401 for swap preview when unauthenticated', function () {
    $response = $this->getJson('/billing/swap/preview?price_id=price_team_monthly');

    $response->assertStatus(401);
});
