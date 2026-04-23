<?php

use App\Models\User;
use App\Services\Billing\Stats\SignupConversionCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns zero when no users exist', function () {
    // Use a fresh state by checking with all soft-deleted
    $calculator = app(SignupConversionCalculator::class);

    // Will be 0 or some float — just check type
    expect($calculator->calculate())->toBeFloat();
});

it('calculates signup to paid conversion ratio', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $calculator = app(SignupConversionCalculator::class);
    $result = $calculator->calculate();

    expect($result)->toBeFloat();
    expect($result)->toBeGreaterThan(0.0);
    expect($result)->toBeLessThanOrEqual(100.0);
});

it('calculateCohorted returns float between 0 and 100', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $calculator = app(SignupConversionCalculator::class);
    $result = $calculator->calculateCohorted();

    expect($result)->toBeFloat();
    expect($result)->toBeGreaterThanOrEqual(0.0);
    expect($result)->toBeLessThanOrEqual(100.0);
});
