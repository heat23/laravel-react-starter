<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;

class CustomerHealthService
{
    /** @var bool|null Cached result of subscriptions table existence check */
    private static ?bool $subscriptionsTableExists = null;

    /** @var array<int, int> Per-request cache of login counts keyed by user ID */
    private array $loginCountCache = [];

    /**
     * Per-request cache of subscription stripe_status keyed by user ID.
     * Null value means no subscription found; false means not yet cached.
     *
     * @var array<int, string|null>
     */
    private array $subscriptionStatusCache = [];

    /**
     * Calculate a health score (0-100) for a user based on 4 dimensions.
     */
    public function calculateHealthScore(User $user): int
    {
        if (! isset($user->settings_count)) {
            $user->loadCount(['settings', 'tokens', 'webhookEndpoints']);
        }

        return $this->loginFrequencyScore($user)
            + $this->featureAdoptionScore($user)
            + $this->billingStatusScore($user)
            + $this->profileCompletionScore($user);
    }

    /**
     * Warm internal caches for a batch of users before calling calculateHealthScore.
     * This pre-populates login counts and billing status to avoid N+1 queries.
     */
    public function primeHealthScoreCaches(Collection $users): void
    {
        $userIds = $users->pluck('id')->all();
        $this->primeLoginCountCache($userIds);
        $this->primeBillingCache($userIds);
    }

    /**
     * Get email verification rate: % of users (last 30 days) who verified email within 7 days of signup.
     * Distinct from ProductAnalyticsService::getOnboardingCompletionRate() which measures
     * onboarding wizard completion.
     */
    public function getEmailVerificationRate(): float
    {
        return Cache::remember('metrics:email_verification_rate', AdminCacheKey::DEFAULT_TTL, function () {
            $totalUsers = User::where('created_at', '>=', now()->subDays(30))->count();

            if ($totalUsers === 0) {
                return 0.0;
            }

            $activatedUsers = User::where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('email_verified_at')
                ->whereRaw('email_verified_at <= '.(DB::getDriverName() === 'sqlite'
                    ? "datetime(created_at, '+7 days')"
                    : 'DATE_ADD(created_at, INTERVAL 7 DAY)'))
                ->count();

            return round(($activatedUsers / $totalUsers) * 100, 1);
        });
    }

    /**
     * Get trial-to-paid conversion rate.
     */
    public function getTrialConversionRate(): float
    {
        return Cache::remember('metrics:trial_conversion_rate', AdminCacheKey::DEFAULT_TTL, function () {
            $trialUsers = User::whereNotNull('trial_ends_at')->count();

            if ($trialUsers === 0) {
                return 0.0;
            }

            $convertedUsers = DB::table('users')
                ->join('subscriptions', 'users.id', '=', 'subscriptions.user_id')
                ->whereNotNull('users.trial_ends_at')
                ->where('subscriptions.stripe_status', 'active')
                ->distinct('users.id')
                ->count('users.id');

            return round(($convertedUsers / $trialUsers) * 100, 1);
        });
    }

    /**
     * D7 retention rate: % of users registered 7–10 days ago who were active by day 7.
     * Cohort window of 3 days reduces noise on low-volume installs.
     */
    public function getD7RetentionRate(): float
    {
        return Cache::remember('metrics:retention_d7', 3600, function () {
            $cohortUsers = User::where('created_at', '>=', now()->subDays(10))
                ->where('created_at', '<', now()->subDays(7))
                ->whereNotNull('email_verified_at')
                ->get(['id', 'created_at', 'last_active_at', 'last_login_at']);

            if ($cohortUsers->isEmpty()) {
                return 0.0;
            }

            $retained = $cohortUsers->filter(function (User $user) {
                $daySevenMark = $user->created_at->addDays(6);
                $lastSeen = $user->last_active_at ?? $user->last_login_at;

                return $lastSeen !== null && $lastSeen >= $daySevenMark;
            })->count();

            return round(($retained / $cohortUsers->count()) * 100, 1);
        });
    }

    /**
     * D30 retention rate: % of users registered 30–33 days ago who were active by day 30.
     */
    public function getD30RetentionRate(): float
    {
        return Cache::remember('metrics:retention_d30', 3600, function () {
            $cohortUsers = User::where('created_at', '>=', now()->subDays(33))
                ->where('created_at', '<', now()->subDays(30))
                ->whereNotNull('email_verified_at')
                ->get(['id', 'created_at', 'last_active_at', 'last_login_at']);

            if ($cohortUsers->isEmpty()) {
                return 0.0;
            }

            $retained = $cohortUsers->filter(function (User $user) {
                $dayThirtyMark = $user->created_at->addDays(29);
                $lastSeen = $user->last_active_at ?? $user->last_login_at;

                return $lastSeen !== null && $lastSeen >= $dayThirtyMark;
            })->count();

            return round(($retained / $cohortUsers->count()) * 100, 1);
        });
    }

    /**
     * Get distribution of users across health score brackets.
     *
     * @return array<string, int>
     */
    public function getHealthDistribution(): array
    {
        return Cache::remember('metrics:health_distribution', AdminCacheKey::DEFAULT_TTL, function () {
            $distribution = ['critical' => 0, 'at_risk' => 0, 'moderate' => 0, 'healthy' => 0];

            User::whereNull('deleted_at')
                ->withCount(['settings', 'tokens', 'webhookEndpoints'])
                ->chunk(100, function ($users) use (&$distribution) {
                    // Pre-fetch all login counts and subscription statuses for this chunk in 2 queries
                    $userIds = $users->pluck('id')->all();
                    $this->primeLoginCountCache($userIds);
                    $this->primeBillingCache($userIds);

                    foreach ($users as $user) {
                        $score = $this->calculateHealthScore($user);
                        match (true) {
                            $score >= 76 => $distribution['healthy']++,
                            $score >= 51 => $distribution['moderate']++,
                            $score >= 26 => $distribution['at_risk']++,
                            default => $distribution['critical']++,
                        };
                    }

                    // Clear per-chunk caches to free memory
                    $this->loginCountCache = [];
                    $this->subscriptionStatusCache = [];
                });

            return $distribution;
        });
    }

    /**
     * Pre-populate the subscription status cache for a batch of user IDs (1 query for N users).
     *
     * @param  int[]  $userIds
     */
    private function primeBillingCache(array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        if (self::$subscriptionsTableExists === null) {
            self::$subscriptionsTableExists = DB::getSchemaBuilder()->hasTable('subscriptions');
        }

        if (! self::$subscriptionsTableExists) {
            foreach ($userIds as $id) {
                $this->subscriptionStatusCache[$id] = null;
            }

            return;
        }

        $statuses = DB::table('subscriptions')
            ->whereIn('user_id', $userIds)
            ->where('type', 'default')
            ->pluck('stripe_status', 'user_id');

        foreach ($userIds as $id) {
            $this->subscriptionStatusCache[$id] = $statuses[$id] ?? null;
        }
    }

    /**
     * Pre-populate the login count cache for a batch of user IDs (1 query for N users).
     *
     * @param  int[]  $userIds
     */
    private function primeLoginCountCache(array $userIds): void
    {
        if (! class_exists(AuditLog::class) || empty($userIds)) {
            return;
        }

        $counts = AuditLog::whereIn('user_id', $userIds)
            ->where('event', 'login')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('user_id')
            ->selectRaw('user_id, count(*) as cnt')
            ->pluck('cnt', 'user_id');

        foreach ($userIds as $id) {
            $this->loginCountCache[$id] = (int) ($counts[$id] ?? 0);
        }
    }

    /**
     * Login frequency score (0-25 points).
     * Based on audit log entries in last 30 days.
     * Uses loginCountCache when populated by primeLoginCountCache() for batch operations.
     */
    private function loginFrequencyScore(User $user): int
    {
        $loginCount = 0;

        if (isset($this->loginCountCache[$user->id])) {
            $loginCount = $this->loginCountCache[$user->id];
        } elseif (class_exists(AuditLog::class)) {
            $loginCount = AuditLog::where('user_id', $user->id)
                ->where('event', 'login')
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
        }

        return match (true) {
            $loginCount >= 11 => 25,
            $loginCount >= 4 => 18,
            $loginCount >= 1 => 10,
            default => 0,
        };
    }

    /**
     * Feature adoption score (0–25 points).
     *
     * Weights mirror EngagementScoringService::featureAdoptionScoreFromCounts()
     * to maintain consistent score-to-conversion correlation across both services.
     * Updated 2026-03: webhooks 12 pts, tokens 10 pts base (+3 depth ≥5), settings 3 pts.
     */
    private function featureAdoptionScore(User $user): int
    {
        $score = 0;

        if ($user->webhook_endpoints_count > 0) {
            $score += 12;
        }

        if ($user->tokens_count > 0) {
            $score += 10;
        }

        if ($user->tokens_count >= 5) {
            $score += 3; // depth signal
        }

        if ($user->settings_count > 0) {
            $score += 3;
        }

        return min($score, 25);
    }

    /**
     * Billing status score (0-25 points).
     * Uses eager-loaded subscriptions when available, falls back to DB query.
     * Uses subscriptionStatusCache when populated by primeBillingCache() for batch operations.
     */
    private function billingStatusScore(User $user): int
    {
        // 1. Use batch cache when populated by primeBillingCache()
        if (array_key_exists($user->id, $this->subscriptionStatusCache)) {
            $stripeStatus = $this->subscriptionStatusCache[$user->id];
        }
        // 2. Use eager-loaded subscriptions relationship if available
        elseif ($user->relationLoaded('subscriptions')) {
            /** @var Subscription|null $subscription */
            $subscription = $user->subscriptions
                ->where('type', 'default')
                ->sortByDesc('created_at')
                ->first();
            $stripeStatus = $subscription?->stripe_status;
        }
        // 3. Fall back to DB query
        else {
            if (self::$subscriptionsTableExists === null) {
                self::$subscriptionsTableExists = DB::getSchemaBuilder()->hasTable('subscriptions');
            }

            if (! self::$subscriptionsTableExists) {
                return 0;
            }

            $stripeStatus = DB::table('subscriptions')
                ->where('user_id', $user->id)
                ->where('type', 'default')
                ->value('stripe_status') ?? null;
        }

        if ($stripeStatus === null) {
            // Check if on trial (trial_ends_at on user)
            /** @var Carbon|null $trialEndsAt */
            $trialEndsAt = $user->trial_ends_at;
            if ($trialEndsAt !== null && $trialEndsAt->isFuture()) {
                return 20;
            }

            return 0;
        }

        return match ($stripeStatus) {
            'active' => 25,
            'trialing' => 20,
            'past_due' => 5,
            default => 0,
        };
    }

    /**
     * Profile completion score (0-25 points).
     */
    private function profileCompletionScore(User $user): int
    {
        $score = 0;

        // Email verified
        if ($user->hasVerifiedEmail()) {
            $score += 10;
        }

        if ($user->settings_count >= 2) {
            $score += 8;
        }

        // Has password set (not just OAuth)
        if ($user->hasPassword()) {
            $score += 7;
        }

        return min($score, 25);
    }
}
