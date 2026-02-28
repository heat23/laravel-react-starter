<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use Illuminate\Support\Facades\Cache;

class CacheInvalidationManager
{
    public function invalidateBilling(): void
    {
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
        Cache::forget(AdminCacheKey::BILLING_STATS->value);
        Cache::forget(AdminCacheKey::BILLING_TIER_DIST->value);
        Cache::forget(AdminCacheKey::BILLING_STATUS->value);
        Cache::forget(AdminCacheKey::BILLING_GROWTH_CHART->value);
        Cache::forget(AdminCacheKey::BILLING_TRIALS->value);
    }

    public function invalidateTokens(): void
    {
        Cache::forget(AdminCacheKey::TOKENS_STATS->value);
        Cache::forget(AdminCacheKey::TOKENS_MOST_ACTIVE->value);
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
    }

    public function invalidateWebhooks(): void
    {
        Cache::forget(AdminCacheKey::WEBHOOKS_STATS->value);
        Cache::forget(AdminCacheKey::WEBHOOKS_DELIVERY_CHART->value);
        Cache::forget(AdminCacheKey::WEBHOOKS_RECENT_FAILURES->value);
    }

    public function invalidateTwoFactor(): void
    {
        Cache::forget(AdminCacheKey::TWO_FACTOR_STATS->value);
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
    }

    public function invalidateSocialAuth(): void
    {
        Cache::forget(AdminCacheKey::SOCIAL_AUTH_STATS->value);
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
    }

    public function invalidateUser(int $userId): void
    {
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
        Cache::forget(AdminCacheKey::BILLING_STATS->value);
        Cache::forget(AdminCacheKey::BILLING_TIER_DIST->value);
        Cache::forget(AdminCacheKey::TOKENS_STATS->value);
        Cache::forget(AdminCacheKey::TWO_FACTOR_STATS->value);
        Cache::forget(AdminCacheKey::featureFlagsUser($userId));
    }

    public function invalidateDashboard(): void
    {
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
    }
}
