<?php

use App\Models\User;
use App\Services\Billing\Stats\TrialConversionCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns zero when no trialed subscriptions exist', function () {
    $calculator = app(TrialConversionCalculator::class);

    expect($calculator->calculate())->toBe(0.0);
});

it('calculates trial conversion counting active and historically-paid subscriptions', function () {
    // 1 converted (active)
    $user1 = User::factory()->create();
    createSubscription($user1, ['stripe_status' => 'active', 'trial_ends_at' => now()->subDays(5)]);

    // 1 not converted (canceled, no ends_at)
    $user2 = User::factory()->create();
    createSubscription($user2, ['stripe_status' => 'canceled', 'trial_ends_at' => now()->subDays(3)]);

    $calculator = app(TrialConversionCalculator::class);

    expect($calculator->calculate())->toBe(50.0);
});

it('counts canceled subscriptions with ends_at as converted (avoids survivor bias)', function () {
    // 10 active (converted and still paying)
    for ($i = 0; $i < 10; $i++) {
        $user = User::factory()->create();
        createSubscription($user, ['stripe_status' => 'active', 'trial_ends_at' => now()->subDays(10)]);
    }

    // 10 churned after converting
    for ($i = 0; $i < 10; $i++) {
        $user = User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'canceled',
            'trial_ends_at' => now()->subDays(10),
            'ends_at' => now()->subDays(2),
        ]);
    }

    // 30 never converted
    for ($i = 0; $i < 30; $i++) {
        $user = User::factory()->create();
        createSubscription($user, ['stripe_status' => 'canceled', 'trial_ends_at' => now()->subDays(10)]);
    }

    $calculator = app(TrialConversionCalculator::class);

    // 20 converted out of 50 = 40%
    expect($calculator->calculate())->toBe(40.0);
});
