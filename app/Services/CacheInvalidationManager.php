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
        Cache::forget(AdminCacheKey::BILLING_STATS->value.'_churn_breakdown');
        Cache::forget(AdminCacheKey::BILLING_TIER_DIST->value);
        Cache::forget(AdminCacheKey::BILLING_STATUS->value);
        Cache::forget(AdminCacheKey::BILLING_GROWTH_CHART->value);
        Cache::forget(AdminCacheKey::BILLING_TRIALS->value);
        Cache::forget(AdminCacheKey::BILLING_COHORT_RETENTION->value);
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

    public function invalidateUserLimitWarnings(int $userId): void
    {
        Cache::forget("user:{$userId}:limit_warnings");
    }

    public function invalidateDashboard(): void
    {
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
    }

    public function invalidateOnRegistration(): void
    {
        Cache::forget(AdminCacheKey::DASHBOARD_STATS->value);
        Cache::forget(AdminCacheKey::DASHBOARD_SIGNUP_CHART->value);
    }

    public function invalidateNotifications(): void
    {
        Cache::forget(AdminCacheKey::NOTIFICATIONS_STATS->value);
        Cache::forget(AdminCacheKey::NOTIFICATIONS_VOLUME->value);
    }

    public function invalidateContactSubmissions(): void
    {
        Cache::forget(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value);
    }

    public function invalidateIndexNow(): void
    {
        Cache::forget(AdminCacheKey::INDEXNOW_STATS->value);
    }

    public function invalidateFeatureFlagsGlobal(): void
    {
        Cache::forget(AdminCacheKey::FEATURE_FLAGS_GLOBAL->value);
    }

    public function invalidateLifecycle(): void
    {
        Cache::forget(AdminCacheKey::STAGE_FUNNEL->value);
    }
}
