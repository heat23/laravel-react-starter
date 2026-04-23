<?php

use App\Services\FeatureFlagValidator;

it('validates a known flag without exception', function () {
    config(['features.webhooks' => ['enabled' => false]]);

    $validator = new FeatureFlagValidator;

    expect(fn () => $validator->validateFlag('webhooks'))->not->toThrow(InvalidArgumentException::class);
});

it('throws for an unknown flag', function () {
    $validator = new FeatureFlagValidator;

    expect(fn () => $validator->validateFlag('nonexistent_flag_xyz'))->toThrow(InvalidArgumentException::class);
});

it('throws for a protected flag on validateNotProtected', function () {
    $validator = new FeatureFlagValidator;

    expect(fn () => $validator->validateNotProtected('admin'))->toThrow(RuntimeException::class);
});

it('does not throw for an unprotected flag on validateNotProtected', function () {
    $validator = new FeatureFlagValidator;

    expect(fn () => $validator->validateNotProtected('webhooks'))->not->toThrow(RuntimeException::class);
});

it('isProtected returns true for admin', function () {
    $validator = new FeatureFlagValidator;

    expect($validator->isProtected('admin'))->toBeTrue();
});

it('isProtected returns false for non-admin flags', function () {
    $validator = new FeatureFlagValidator;

    expect($validator->isProtected('billing'))->toBeFalse();
});

it('getDefinedFlags returns only flags with enabled key', function () {
    config(['features' => [
        'billing' => ['enabled' => false],
        'webhooks' => ['enabled' => true],
        'other_section' => ['timeout' => 30],
    ]]);

    $validator = new FeatureFlagValidator;
    $flags = $validator->getDefinedFlags();

    expect($flags)->toHaveKeys(['billing', 'webhooks'])
        ->not->toHaveKey('other_section');
});
