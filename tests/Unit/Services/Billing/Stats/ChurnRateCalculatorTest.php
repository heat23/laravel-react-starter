<?php

use App\Models\User;
use App\Services\Billing\Stats\ChurnRateCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns zero when no subscriptions exist', function () {
    $calculator = app(ChurnRateCalculator::class);

    expect($calculator->calculate())->toBe(0.0);
});

it('calculates churn rate as canceled over active at period start', function () {
    $thirtyOneDaysAgo = now()->subDays(31);

    $user1 = User::factory()->create();
    $sub1 = createSubscription($user1);
    $sub1->forceFill([
        'created_at' => $thirtyOneDaysAgo,
        'ends_at' => now()->subDays(5),
    ])->saveQuietly();

    $user2 = User::factory()->create();
    $sub2 = createSubscription($user2);
    $sub2->forceFill(['created_at' => $thirtyOneDaysAgo])->saveQuietly();

    $calculator = app(ChurnRateCalculator::class);

    expect($calculator->calculate())->toBe(50.0);
});

it('excludes trialing subscriptions from the denominator', function () {
    $thirtyOneDaysAgo = now()->subDays(31);

    for ($i = 0; $i < 10; $i++) {
        $user = User::factory()->create();
        $sub = createSubscription($user, ['stripe_status' => 'active']);
        $sub->forceFill(['created_at' => $thirtyOneDaysAgo])->saveQuietly();
    }

    for ($i = 0; $i < 40; $i++) {
        $user = User::factory()->create();
        $sub = createSubscription($user, ['stripe_status' => 'trialing', 'trial_ends_at' => now()->addDays(7)]);
        $sub->forceFill(['created_at' => $thirtyOneDaysAgo])->saveQuietly();
    }

    for ($i = 0; $i < 1; $i++) {
        $user = User::factory()->create();
        $sub = createSubscription($user, ['stripe_status' => 'canceled', 'ends_at' => now()->subDays(5)]);
        $sub->forceFill(['created_at' => $thirtyOneDaysAgo])->saveQuietly();
    }

    $calculator = app(ChurnRateCalculator::class);

    // churn = 1/10 = 10%, not 1/50 = 2%
    expect($calculator->calculate())->toBe(10.0);
});
