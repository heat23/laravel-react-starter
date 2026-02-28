<?php

use App\Exceptions\ConcurrentOperationException;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();

    // Configure test plan tiers
    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.stripe_price_annual' => 'price_pro_annual',
        'plans.pro.name' => 'Pro',
        'plans.pro.per_seat' => false,

        'plans.team.stripe_price_monthly' => 'price_team_monthly',
        'plans.team.stripe_price_annual' => 'price_team_annual',
        'plans.team.name' => 'Team',
        'plans.team.per_seat' => true,
        'plans.team.min_seats' => 3,
        'plans.team.limits.seats' => 50,

        'plans.enterprise.stripe_price_monthly' => 'price_enterprise_monthly',
        'plans.enterprise.stripe_price_annual' => 'price_enterprise_annual',
        'plans.enterprise.name' => 'Enterprise',
        'plans.enterprise.per_seat' => true,
        'plans.enterprise.min_seats' => 10,
        'plans.enterprise.limits.seats' => null, // unlimited
    ]);

    $this->service = app(BillingService::class);
});

// NOTE: Tests that call Cashier methods (create, cancel, resume, swap, updateQuantity)
// are intentionally omitted because they hit the real Stripe API. These tests focus on
// the BillingService's critical logic: Redis locks, tier resolution, validation, and status queries.
//
// For Stripe integration testing, see feature tests that use the createSubscription() helper
// to bypass API calls and test end-to-end behavior.

it('resolves tier from pro monthly price', function () {
    $tier = $this->service->resolveTierFromPrice('price_pro_monthly');
    expect($tier)->toBe('pro');
});

it('resolves tier from pro annual price', function () {
    $tier = $this->service->resolveTierFromPrice('price_pro_annual');
    expect($tier)->toBe('pro');
});

it('resolves tier from team monthly price', function () {
    $tier = $this->service->resolveTierFromPrice('price_team_monthly');
    expect($tier)->toBe('team');
});

it('resolves tier from enterprise monthly price', function () {
    $tier = $this->service->resolveTierFromPrice('price_enterprise_monthly');
    expect($tier)->toBe('enterprise');
});

it('resolves null for unknown price', function () {
    $tier = $this->service->resolveTierFromPrice('price_unknown');
    expect($tier)->toBeNull();
});

it('validates seat count for single-seat plan', function () {
    $error = $this->service->validateSeatCount('pro', 1);
    expect($error)->toBeNull();
});

it('rejects multiple seats for single-seat plan', function () {
    $error = $this->service->validateSeatCount('pro', 5);
    expect($error)->toBe('This plan does not support per-seat billing.');
});

it('validates seat count within team plan limits', function () {
    $error = $this->service->validateSeatCount('team', 5);
    expect($error)->toBeNull();
});

it('rejects seat count below team plan minimum', function () {
    $error = $this->service->validateSeatCount('team', 2);
    expect($error)->toBe('This plan requires a minimum of 3 seats.');
});

it('rejects seat count above team plan maximum', function () {
    $error = $this->service->validateSeatCount('team', 51);
    expect($error)->toBe('This plan supports a maximum of 50 seats.');
});

it('validates enterprise seat count with no maximum', function () {
    $error = $this->service->validateSeatCount('enterprise', 100);
    expect($error)->toBeNull();
});

it('rejects seat count below enterprise plan minimum', function () {
    $error = $this->service->validateSeatCount('enterprise', 5);
    expect($error)->toBe('This plan requires a minimum of 10 seats.');
});

it('rejects invalid tier', function () {
    $error = $this->service->validateSeatCount('invalid_tier', 1);
    expect($error)->toBe('Invalid plan tier.');
});

it('gets subscription status for user without subscription', function () {
    $user = User::factory()->create();

    $status = $this->service->getSubscriptionStatus($user);

    expect($status)->toMatchArray([
        'subscribed' => false,
        'tier' => 'free',
        'status' => null,
        'on_trial' => false,
        'on_grace_period' => false,
        'quantity' => 1,
        'ends_at' => null,
        'trial_ends_at' => null,
    ]);
});

it('gets subscription status for user with platform trial', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    $status = $this->service->getSubscriptionStatus($user);

    expect($status['subscribed'])->toBeFalse()
        ->and($status['on_trial'])->toBeTrue()
        ->and($status['trial_ends_at'])->toBeString();
});

it('gets subscription status for active subscription', function () {
    $user = User::factory()->create();
    $subscription = createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
        'ends_at' => null,
    ]);

    $status = $this->service->getSubscriptionStatus($user);

    expect($status)->toMatchArray([
        'subscribed' => true,
        'tier' => 'pro',
        'status' => 'active',
        'on_trial' => false,
        'on_grace_period' => false,
        'quantity' => 1,
        'ends_at' => null,
    ]);
});

it('gets subscription status for canceled subscription on grace period', function () {
    $user = User::factory()->create();
    $subscription = createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
        'ends_at' => now()->addDays(7),
    ]);

    $status = $this->service->getSubscriptionStatus($user);

    expect($status['subscribed'])->toBeTrue()
        ->and($status['on_grace_period'])->toBeTrue()
        ->and($status['ends_at'])->toBeString();
});

it('resolves user tier for free user', function () {
    $user = User::factory()->create();

    $tier = $this->service->resolveUserTier($user);

    expect($tier)->toBe('free');
});

it('resolves user tier for subscribed user', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
    ]);

    $tier = $this->service->resolveUserTier($user);

    expect($tier)->toBe('pro');
});

it('resolves user tier for expired subscription', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'stripe_price' => 'price_pro_monthly',
        'ends_at' => now()->subDays(1),
    ]);

    $tier = $this->service->resolveUserTier($user);

    expect($tier)->toBe('free');
});

it('throws exception when Redis lock cannot be acquired for subscription creation', function () {
    $user = User::factory()->create();

    // Acquire lock manually to simulate concurrent operation
    $lock = Cache::lock("subscription:create:{$user->id}", 35);
    $lock->get();

    try {
        $this->service->createSubscription($user, 'price_pro_monthly');
        expect(true)->toBeFalse(); // Should not reach here
    } catch (ConcurrentOperationException $e) {
        expect($e)->toBeInstanceOf(ConcurrentOperationException::class);
    } finally {
        $lock->release();
    }
});
