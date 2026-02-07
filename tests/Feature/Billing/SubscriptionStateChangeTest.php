<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

// ============================================
// Active subscription states
// ============================================

it('detects active subscription', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_status' => 'active', 'ends_at' => null]);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->active())->toBeTrue();
    expect($subscription->canceled())->toBeFalse();
    expect($subscription->onGracePeriod())->toBeFalse();
});

// ============================================
// Canceled with grace period
// ============================================

it('detects canceled subscription in grace period', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'active',
        'ends_at' => now()->addDays(10),
    ]);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->canceled())->toBeTrue();
    expect($subscription->onGracePeriod())->toBeTrue();
    expect($subscription->active())->toBeTrue(); // still active during grace
});

// ============================================
// Canceled and expired
// ============================================

it('detects fully expired subscription', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->canceled())->toBeTrue();
    expect($subscription->onGracePeriod())->toBeFalse();
    expect($subscription->ended())->toBeTrue();
});

// ============================================
// Resume during grace period
// ============================================

it('allows resuming canceled subscription in grace period via controller', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'ends_at' => now()->addDays(10),
    ]);

    // Mock the BillingService to avoid Stripe API call
    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resumeSubscription')->once()->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/resume');

    $response->assertRedirect(route('billing.index'));
    $response->assertSessionHas('success');
});

it('prevents resuming after grace period expired', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)->post('/billing/resume');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

// ============================================
// Past due
// ============================================

it('detects past due subscription', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_status' => 'past_due']);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->pastDue())->toBeTrue();
});

it('transitions from past due to active when payment succeeds', function () {
    $user = User::factory()->create();
    $subscription = createSubscription($user, ['stripe_status' => 'past_due']);

    // Simulate payment succeeding (Cashier updates this via webhook)
    $subscription->update(['stripe_status' => 'active']);

    $freshSubscription = $user->fresh()->subscription('default');

    expect($freshSubscription->active())->toBeTrue();
    expect($freshSubscription->pastDue())->toBeFalse();
});

it('transitions from past due to canceled after failed retries', function () {
    $user = User::factory()->create();
    $subscription = createSubscription($user, ['stripe_status' => 'past_due']);

    $subscription->update([
        'stripe_status' => 'canceled',
        'ends_at' => now(),
    ]);

    $freshSubscription = $user->fresh()->subscription('default');

    expect($freshSubscription->ended())->toBeTrue();
});

// ============================================
// Trial states
// ============================================

it('detects subscription on trial', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'trialing',
        'trial_ends_at' => now()->addDays(14),
    ]);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->onTrial())->toBeTrue();
});

it('transitions from trial to active when trial ends with valid payment', function () {
    $user = User::factory()->create();
    $subscription = createSubscription($user, [
        'stripe_status' => 'trialing',
        'trial_ends_at' => now()->addDays(14),
    ]);

    // Simulate trial ending with successful payment
    $subscription->update([
        'stripe_status' => 'active',
        'trial_ends_at' => now()->subMinute(),
    ]);

    $freshSubscription = $user->fresh()->subscription('default');

    expect($freshSubscription->onTrial())->toBeFalse();
    expect($freshSubscription->active())->toBeTrue();
});

it('transitions from trial to past due when payment fails at trial end', function () {
    $user = User::factory()->create();
    $subscription = createSubscription($user, [
        'stripe_status' => 'trialing',
        'trial_ends_at' => now()->addDays(14),
    ]);

    $subscription->update([
        'stripe_status' => 'past_due',
        'trial_ends_at' => now()->subMinute(),
    ]);

    $freshSubscription = $user->fresh()->subscription('default');

    expect($freshSubscription->pastDue())->toBeTrue();
    expect($freshSubscription->onTrial())->toBeFalse();
});

// ============================================
// Incomplete (SCA/3DS)
// ============================================

it('detects incomplete subscription requiring payment confirmation', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_status' => 'incomplete']);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->hasIncompletePayment())->toBeTrue();
});

it('detects incomplete expired subscription', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_status' => 'incomplete_expired']);

    $subscription = $user->fresh()->subscription('default');

    expect($subscription->active())->toBeFalse();
});

// ============================================
// Cancel via controller
// ============================================

it('allows canceling subscription via controller', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('cancelSubscription')->once()->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/cancel');

    $response->assertRedirect(route('billing.index'));
    $response->assertSessionHas('success');
});

it('allows canceling subscription immediately', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('cancelSubscription')
        ->once()
        ->withArgs(fn ($u, $immediately) => $immediately === true)
        ->andReturn($user->subscription('default'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/cancel', ['immediately' => true]);

    $response->assertRedirect(route('billing.index'));
    $response->assertSessionHas('success', 'Subscription canceled immediately.');
});

it('prevents canceling when no active subscription', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/billing/cancel');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

it('handles Stripe API error during cancellation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('cancelSubscription')
        ->once()
        ->andThrow(new \Stripe\Exception\ApiConnectionException('Stripe timeout'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/cancel');

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Unable to process your request. Please try again.');
});

it('handles Stripe API error during resume', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['ends_at' => now()->addDays(10)]);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resumeSubscription')
        ->once()
        ->andThrow(new \Stripe\Exception\ApiConnectionException('Stripe timeout'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/resume');

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Unable to process your request. Please try again.');
});

// ============================================
// Authentication
// ============================================

it('requires authentication to resume', function () {
    $response = $this->post('/billing/resume');

    $response->assertRedirect(route('login'));
});
