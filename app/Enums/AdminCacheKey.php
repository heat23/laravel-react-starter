<?php

namespace App\Enums;

use App\Models\FeatureFlagOverride;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

enum AdminCacheKey: string
{
    // Dashboard
    case DASHBOARD_STATS = 'admin:dashboard:stats';
    case DASHBOARD_SIGNUP_CHART = 'admin:dashboard:signup_chart';

    // Billing
    case BILLING_STATS = 'admin:billing:stats';
    case BILLING_TIER_DIST = 'admin:billing:tier_dist';
    case BILLING_STATUS = 'admin:billing:status';
    case BILLING_GROWTH_CHART = 'admin:billing:growth_chart';
    case BILLING_TRIALS = 'admin:billing:trials';
    case BILLING_COHORT_RETENTION = 'admin:billing:cohort_retention';

    // Audit Logs
    case AUDIT_EVENT_TYPES = 'admin:audit_logs:event_types';

    // Social Auth
    case SOCIAL_AUTH_STATS = 'admin:social_auth:stats';

    // Webhooks
    case WEBHOOKS_STATS = 'admin:webhooks:stats';
    case WEBHOOKS_DELIVERY_CHART = 'admin:webhooks:delivery_chart';
    case WEBHOOKS_RECENT_FAILURES = 'admin:webhooks:recent_failures';

    // IndexNow
    case INDEXNOW_STATS = 'admin:indexnow:stats';
    case INDEXNOW_RECENT_SUBMISSIONS = 'admin:indexnow:recent_submissions';

    // Tokens
    case TOKENS_STATS = 'admin:tokens:stats';
    case TOKENS_MOST_ACTIVE = 'admin:tokens:most_active';

    // Two-Factor
    case TWO_FACTOR_STATS = 'admin:two_factor:stats';

    // Notifications
    case NOTIFICATIONS_STATS = 'admin:notifications:stats';
    case NOTIFICATIONS_VOLUME = 'admin:notifications:volume';

    // Contact Submissions
    case CONTACT_SUBMISSIONS_STATS = 'admin:contact_submissions:stats';

    // Feature Flags
    case FEATURE_FLAGS_GLOBAL = 'admin:feature_flags:global';

    // Lifecycle / CRM
    case STAGE_FUNNEL = 'admin:lifecycle:stage_funnel';
    case STAGE_VELOCITY = 'admin:lifecycle:stage_velocity';

    /** Default TTL in seconds for most admin caches. */
    public const DEFAULT_TTL = 300;

    /** Longer TTL for chart data that changes infrequently. */
    public const CHART_TTL = 3600;

    /**
     * Flush all admin caches.
     */
    public static function flushAll(): void
    {
        foreach (self::cases() as $key) {
            Cache::forget($key->value);
        }

        // Flush per-user feature flag caches
        try {
            $userIds = FeatureFlagOverride::distinct()
                ->whereNotNull('user_id')
                ->pluck('user_id');

            foreach ($userIds as $userId) {
                Cache::forget(self::featureFlagsUser($userId));
            }
        } catch (QueryException) {
            // Table doesn't exist yet (fresh install) — nothing to flush
        }
    }

    /**
     * Get the cache key for user-specific feature flag overrides.
     */
    public static function featureFlagsUser(int $userId): string
    {
        return "admin:feature_flags:user:{$userId}";
    }
}
