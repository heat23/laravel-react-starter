<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('resolves user tier from pro monthly price', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $service = new BillingService;
    expect($service->resolveUserTier($user->fresh()))->toBe('pro');
});

it('resolves user tier from team price', function () {
    $user = User::factory()->create();
    createTeamSubscription($user, 5);

    $service = new BillingService;
    expect($service->resolveUserTier($user->fresh()))->toBe('team');
});

it('resolves user tier from enterprise price', function () {
    $user = User::factory()->create();
    createEnterpriseSubscription($user, 10);

    $service = new BillingService;
    expect($service->resolveUserTier($user->fresh()))->toBe('enterprise');
});

it('returns free tier for user without subscription', function () {
    $user = User::factory()->create();

    $service = new BillingService;
    expect($service->resolveUserTier($user))->toBe('free');
});

it('validates seat count rejects non-per-seat plan with quantity > 1', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount('pro', 5);

    expect($error)->toBe('This plan does not support per-seat billing.');
});

it('validates seat count enforces team minimum seats', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount('team', 2);

    expect($error)->toBe('This plan requires a minimum of 3 seats.');
});

it('validates seat count enforces enterprise minimum seats', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount('enterprise', 5);

    expect($error)->toBe('This plan requires a minimum of 10 seats.');
});

it('validates seat count allows valid team quantity', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount('team', 10);

    expect($error)->toBeNull();
});

it('validates seat count enforces team max seats', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount('team', 100);

    expect($error)->toBe('This plan supports a maximum of 50 seats.');
});

it('resolves tier from price id correctly', function () {
    $service = new BillingService;

    expect($service->resolveTierFromPrice('price_pro_monthly'))->toBe('pro');
    expect($service->resolveTierFromPrice('price_pro_annual'))->toBe('pro');
    expect($service->resolveTierFromPrice('price_team_monthly'))->toBe('team');
    expect($service->resolveTierFromPrice('price_team_annual'))->toBe('team');
    expect($service->resolveTierFromPrice('price_enterprise_monthly'))->toBe('enterprise');
    expect($service->resolveTierFromPrice('price_enterprise_annual'))->toBe('enterprise');
    expect($service->resolveTierFromPrice('price_unknown'))->toBeNull();
});

it('returns comprehensive subscription status for subscribed user', function () {
    $user = User::factory()->create();
    createTeamSubscription($user, 8);

    $service = new BillingService;
    $status = $service->getSubscriptionStatus($user->fresh());

    expect($status['subscribed'])->toBeTrue();
    expect($status['tier'])->toBe('team');
    expect($status['status'])->toBe('active');
    expect($status['on_trial'])->toBeFalse();
    expect($status['on_grace_period'])->toBeFalse();
    expect($status['quantity'])->toBe(8);
});

it('returns comprehensive subscription status for unsubscribed user', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);

    $service = new BillingService;
    $status = $service->getSubscriptionStatus($user);

    expect($status['subscribed'])->toBeFalse();
    expect($status['tier'])->toBe('free');
    expect($status['status'])->toBeNull();
    expect($status['on_trial'])->toBeFalse();
    expect($status['quantity'])->toBe(1);
});
