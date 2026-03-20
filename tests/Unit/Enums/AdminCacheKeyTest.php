<?php

use App\Enums\AdminCacheKey;
use Illuminate\Support\Facades\Cache;

it('flushAll clears a value stored at BILLING_COHORT_RETENTION (KPI-007)', function () {
    Cache::put(AdminCacheKey::BILLING_COHORT_RETENTION->value, ['some' => 'data'], 3600);

    expect(Cache::has(AdminCacheKey::BILLING_COHORT_RETENTION->value))->toBeTrue();

    AdminCacheKey::flushAll();

    expect(Cache::has(AdminCacheKey::BILLING_COHORT_RETENTION->value))->toBeFalse();
});

it('BILLING_COHORT_RETENTION has the expected string value', function () {
    expect(AdminCacheKey::BILLING_COHORT_RETENTION->value)->toBe('admin:billing:cohort_retention');
});

it('flushAll clears all registered cache keys', function () {
    foreach (AdminCacheKey::cases() as $key) {
        Cache::put($key->value, 'test', 3600);
    }

    AdminCacheKey::flushAll();

    foreach (AdminCacheKey::cases() as $key) {
        expect(Cache::has($key->value))->toBeFalse();
    }
});
