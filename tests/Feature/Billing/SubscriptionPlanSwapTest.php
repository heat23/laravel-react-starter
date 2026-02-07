<?php

use App\Models\User;
use App\Services\BillingService;

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

    $response->assertRedirect(route('billing.index'));
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

    $response->assertRedirect(route('billing.index'));
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

    $response->assertRedirect(route('billing.index'));
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

    $response->assertRedirect(route('billing.index'));
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

    $response->assertRedirect(route('billing.index'));
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
    $paymentIntent = \Stripe\PaymentIntent::constructFrom(['id' => 'pi_swap_sca', 'status' => 'requires_action']);
    $payment = new \Laravel\Cashier\Payment($paymentIntent);
    $mock->shouldReceive('swapPlan')
        ->once()
        ->andThrow(new \Laravel\Cashier\Exceptions\IncompletePayment($payment));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

it('handles Stripe API error during swap', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('swapPlan')
        ->once()
        ->andThrow(new \Stripe\Exception\ApiConnectionException('Stripe API unavailable'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_team_monthly',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Unable to process your request. Please try again.');
});
