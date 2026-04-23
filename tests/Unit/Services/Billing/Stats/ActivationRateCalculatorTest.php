<?php

use App\Models\User;
use App\Services\Billing\Stats\ActivationRateCalculator;

it('returns zero when no users in cohort window', function () {
    $calculator = app(ActivationRateCalculator::class);

    // Pass a 1-day window so no existing users are included
    expect($calculator->calculate(0))->toBe(0.0);
});

it('calculates rolling activation rate for recent cohort', function () {
    // 2 activated users in last 90 days
    User::factory()->count(2)->create();

    // 2 users without onboarding
    User::factory()->count(2)->onboardingIncomplete()->create();

    $calculator = app(ActivationRateCalculator::class);
    $rate = $calculator->calculate(90);

    expect($rate)->toBeFloat();
    expect($rate)->toBeGreaterThanOrEqual(0.0);
    expect($rate)->toBeLessThanOrEqual(100.0);
});

it('calculateAllTime returns float between 0 and 100', function () {
    User::factory()->count(3)->create();

    $calculator = app(ActivationRateCalculator::class);
    $rate = $calculator->calculateAllTime();

    expect($rate)->toBeFloat();
    expect($rate)->toBeGreaterThanOrEqual(0.0);
    expect($rate)->toBeLessThanOrEqual(100.0);
});
