<?php

use App\Enums\PlanTier;
use App\Support\Billing\PlanLimitResult;

it('constructs an allowed result with defaults', function () {
    $result = new PlanLimitResult(allowed: true);

    expect($result->allowed)->toBeTrue()
        ->and($result->reason)->toBeNull()
        ->and($result->upgradeTier)->toBeNull()
        ->and($result->userMessage)->toBeNull();
});

it('constructs a denied result with upgrade tier', function () {
    $result = new PlanLimitResult(
        allowed: false,
        reason: 'limit_exceeded',
        upgradeTier: PlanTier::Pro,
        userMessage: 'Upgrade to Pro to create more tokens.',
    );

    expect($result->allowed)->toBeFalse()
        ->and($result->reason)->toBe('limit_exceeded')
        ->and($result->upgradeTier)->toBe(PlanTier::Pro)
        ->and($result->userMessage)->toBe('Upgrade to Pro to create more tokens.');
});

it('is readonly — properties cannot be reassigned', function () {
    $result = new PlanLimitResult(allowed: true);

    expect(fn () => $result->allowed = false)->toThrow(Error::class);
});

it('allows null upgradeTier for denied results on highest tier', function () {
    $result = new PlanLimitResult(
        allowed: false,
        reason: 'limit_exceeded',
        upgradeTier: null,
        userMessage: 'You have reached the limit for your plan.',
    );

    expect($result->allowed)->toBeFalse()
        ->and($result->upgradeTier)->toBeNull();
});
