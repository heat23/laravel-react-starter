<?php

use App\Enums\AdminCacheKey;
use App\Services\CacheInvalidationManager;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('invalidates billing caches', function () {
    // Pre-populate caches
    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, 'stats', 300);
    Cache::put(AdminCacheKey::BILLING_STATS->value, 'billing', 300);
    Cache::put(AdminCacheKey::BILLING_TIER_DIST->value, 'tiers', 300);
    Cache::put(AdminCacheKey::BILLING_STATUS->value, 'status', 300);
    Cache::put(AdminCacheKey::BILLING_GROWTH_CHART->value, 'growth', 300);
    Cache::put(AdminCacheKey::BILLING_TRIALS->value, 'trials', 300);

    $manager = app(CacheInvalidationManager::class);
    $manager->invalidateBilling();

    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::BILLING_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::BILLING_TIER_DIST->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::BILLING_STATUS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::BILLING_GROWTH_CHART->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::BILLING_TRIALS->value))->toBeFalse();
});

it('invalidates token caches', function () {
    Cache::put(AdminCacheKey::TOKENS_STATS->value, 'stats', 300);
    Cache::put(AdminCacheKey::TOKENS_MOST_ACTIVE->value, 'active', 300);
    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, 'dashboard', 300);

    $manager = app(CacheInvalidationManager::class);
    $manager->invalidateTokens();

    expect(Cache::has(AdminCacheKey::TOKENS_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::TOKENS_MOST_ACTIVE->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
});

it('invalidates webhook caches', function () {
    Cache::put(AdminCacheKey::WEBHOOKS_STATS->value, 'stats', 300);
    Cache::put(AdminCacheKey::WEBHOOKS_DELIVERY_CHART->value, 'chart', 300);
    Cache::put(AdminCacheKey::WEBHOOKS_RECENT_FAILURES->value, 'failures', 300);

    $manager = app(CacheInvalidationManager::class);
    $manager->invalidateWebhooks();

    expect(Cache::has(AdminCacheKey::WEBHOOKS_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::WEBHOOKS_DELIVERY_CHART->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::WEBHOOKS_RECENT_FAILURES->value))->toBeFalse();
});

it('invalidates two-factor caches', function () {
    Cache::put(AdminCacheKey::TWO_FACTOR_STATS->value, 'stats', 300);
    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, 'dashboard', 300);

    $manager = app(CacheInvalidationManager::class);
    $manager->invalidateTwoFactor();

    expect(Cache::has(AdminCacheKey::TWO_FACTOR_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
});

it('invalidates social auth caches', function () {
    Cache::put(AdminCacheKey::SOCIAL_AUTH_STATS->value, 'stats', 300);
    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, 'dashboard', 300);

    $manager = app(CacheInvalidationManager::class);
    $manager->invalidateSocialAuth();

    expect(Cache::has(AdminCacheKey::SOCIAL_AUTH_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
});

it('invalidates user-related caches including feature flags', function () {
    ensureFeatureFlagOverridesTableExists();

    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, 'stats', 300);
    Cache::put(AdminCacheKey::BILLING_STATS->value, 'billing', 300);
    Cache::put(AdminCacheKey::BILLING_TIER_DIST->value, 'tiers', 300);
    Cache::put(AdminCacheKey::TOKENS_STATS->value, 'tokens', 300);
    Cache::put(AdminCacheKey::TWO_FACTOR_STATS->value, '2fa', 300);
    Cache::put(AdminCacheKey::featureFlagsUser(42), 'flags', 300);

    $manager = app(CacheInvalidationManager::class);
    $manager->invalidateUser(42);

    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::BILLING_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::BILLING_TIER_DIST->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::TOKENS_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::TWO_FACTOR_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::featureFlagsUser(42)))->toBeFalse();
});

it('invalidates dashboard stats only', function () {
    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, 'stats', 300);
    // Other caches should remain
    Cache::put(AdminCacheKey::TOKENS_STATS->value, 'tokens', 300);

    $manager = app(CacheInvalidationManager::class);
    $manager->invalidateDashboard();

    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
    expect(Cache::has(AdminCacheKey::TOKENS_STATS->value))->toBeTrue();
});
