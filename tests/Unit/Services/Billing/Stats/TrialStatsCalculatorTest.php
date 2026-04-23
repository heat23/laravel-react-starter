<?php

use App\Models\User;
use App\Services\Billing\Stats\TrialStatsCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns active_trials and expiring_soon integer counts', function () {
    $calculator = app(TrialStatsCalculator::class);
    $result = $calculator->calculate();

    expect($result)->toHaveKeys(['active_trials', 'expiring_soon']);
    expect($result['active_trials'])->toBeInt();
    expect($result['expiring_soon'])->toBeInt();
});

it('counts active trialing subscriptions with future trial_ends_at', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'trialing',
        'trial_ends_at' => now()->addDays(10),
    ]);

    $calculator = app(TrialStatsCalculator::class);
    $result = $calculator->calculate();

    expect($result['active_trials'])->toBeGreaterThanOrEqual(1);
});

it('counts subscriptions expiring within 3 days in expiring_soon', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'trialing',
        'trial_ends_at' => now()->addDays(2),
    ]);

    $calculator = app(TrialStatsCalculator::class);
    $result = $calculator->calculate();

    expect($result['expiring_soon'])->toBeGreaterThanOrEqual(1);
});
