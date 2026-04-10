<?php

use App\Enums\AdminCacheKey;
use App\Models\FeatureFlagOverride;
use App\Models\User;
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

it('DEFAULT_TTL is 300 seconds (5 minutes)', function () {
    expect(AdminCacheKey::DEFAULT_TTL)->toBe(300);
});

it('CHART_TTL is 3600 seconds (1 hour)', function () {
    expect(AdminCacheKey::CHART_TTL)->toBe(3600);
});

it('featureFlagsUser generates the correct per-user cache key', function () {
    expect(AdminCacheKey::featureFlagsUser(42))->toBe('admin:feature_flags:user:42');
    expect(AdminCacheKey::featureFlagsUser(1))->toBe('admin:feature_flags:user:1');
});

it('flushAll clears per-user feature flag cache when a user override exists', function () {
    ensureFeatureFlagOverridesTableExists();

    $userId = User::factory()->create()->id;
    FeatureFlagOverride::create([
        'user_id' => $userId,
        'flag' => 'billing',
        'enabled' => true,
    ]);

    Cache::put(AdminCacheKey::featureFlagsUser($userId), ['flags' => true], 300);

    AdminCacheKey::flushAll();

    expect(Cache::has(AdminCacheKey::featureFlagsUser($userId)))->toBeFalse();
});
