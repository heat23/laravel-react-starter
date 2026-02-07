<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('allows adding seats to team subscription', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createTeamSubscription($user, 5);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('updateQuantity')
        ->once()
        ->withArgs(fn ($u, $qty) => $qty === 8)
        ->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 8,
    ]);

    $response->assertRedirect(route('billing.index'));
    $response->assertSessionHas('success');
});

it('allows removing seats from team subscription above minimum', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createTeamSubscription($user, 10);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('updateQuantity')
        ->once()
        ->withArgs(fn ($u, $qty) => $qty === 5)
        ->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 5,
    ]);

    $response->assertRedirect(route('billing.index'));
});

it('cannot reduce below min_seats for team plan', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createTeamSubscription($user, 5);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 1, // team min is 3
    ]);

    $response->assertSessionHas('error');
});

it('cannot reduce below min_seats for enterprise plan', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createEnterpriseSubscription($user, 15);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 5, // enterprise min is 10
    ]);

    $response->assertSessionHas('error');
});

it('allows updating quantity directly for enterprise subscription', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createEnterpriseSubscription($user, 10);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('updateQuantity')
        ->once()
        ->withArgs(fn ($u, $qty) => $qty === 25)
        ->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 25,
    ]);

    $response->assertRedirect(route('billing.index'));
});

it('rejects quantity change for pro plan', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 5,
    ]);

    $response->assertSessionHas('error');
});

it('quantity persists in database after direct creation', function () {
    $user = User::factory()->create();
    createTeamSubscription($user, 7);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->quantity)->toBe(7);
});

it('seat count reflected in subscription status', function () {
    $user = User::factory()->create();
    createTeamSubscription($user, 12);

    $status = app(BillingService::class)->getSubscriptionStatus($user->fresh());

    expect($status['quantity'])->toBe(12);
    expect($status['tier'])->toBe('team');
});

it('validates quantity is positive integer', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createTeamSubscription($user, 5);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 0,
    ]);

    $response->assertSessionHasErrors('quantity');
});

it('requires active subscription to update quantity', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 5,
    ]);

    $response->assertSessionHas('error');
});

it('handles IncompletePayment on quantity update', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createTeamSubscription($user, 5);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $paymentIntent = \Stripe\PaymentIntent::constructFrom(['id' => 'pi_qty_sca', 'status' => 'requires_action']);
    $payment = new \Laravel\Cashier\Payment($paymentIntent);
    $mock->shouldReceive('updateQuantity')
        ->once()
        ->andThrow(new \Laravel\Cashier\Exceptions\IncompletePayment($payment));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 8,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
