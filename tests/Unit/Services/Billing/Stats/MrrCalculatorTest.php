<?php

use App\Models\User;
use App\Services\Billing\Stats\MrrCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns zero when no active subscriptions exist', function () {
    $calculator = app(MrrCalculator::class);

    expect($calculator->calculate())->toBe(0.0);
});

it('calculates MRR from monthly price for a single active subscription', function () {
    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.price_monthly' => 29,
    ]);

    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $calculator = app(MrrCalculator::class);

    expect($calculator->calculate())->toBe(29.0);
});

it('calculates MRR from annual price divided by 12', function () {
    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.stripe_price_annual' => 'price_pro_annual',
        'plans.pro.price_annual' => 240,
    ]);

    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_annual']);

    $calculator = app(MrrCalculator::class);

    expect($calculator->calculate())->toBe(20.0);
});

it('applies quantity multiplier to MRR', function () {
    config([
        'plans.team.stripe_price_monthly' => 'price_team_monthly',
        'plans.team.price_monthly' => 15,
    ]);

    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_team_monthly', 'quantity' => 5]);

    $calculator = app(MrrCalculator::class);

    expect($calculator->calculate())->toBe(75.0);
});
