<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomerHealthService
{
    /**
     * Calculate a health score (0-100) for a user based on 4 dimensions.
     */
    public function calculateHealthScore(User $user): int
    {
        return $this->loginFrequencyScore($user)
            + $this->featureAdoptionScore($user)
            + $this->billingStatusScore($user)
            + $this->profileCompletionScore($user);
    }

    /**
     * Get activation rate: % of users who verified email within 7 days of signup.
     */
    public function getActivationRate(): float
    {
        return Cache::remember('metrics:activation_rate', AdminCacheKey::DEFAULT_TTL, function () {
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
     * Get distribution of users across health score brackets.
     *
     * @return array<string, int>
     */
    public function getHealthDistribution(): array
    {
        return Cache::remember('metrics:health_distribution', AdminCacheKey::DEFAULT_TTL, function () {
            $distribution = ['critical' => 0, 'at_risk' => 0, 'moderate' => 0, 'healthy' => 0];

            User::whereNull('deleted_at')
                ->chunk(100, function ($users) use (&$distribution) {
                    foreach ($users as $user) {
                        $score = $this->calculateHealthScore($user);
                        match (true) {
                            $score >= 76 => $distribution['healthy']++,
                            $score >= 51 => $distribution['moderate']++,
                            $score >= 26 => $distribution['at_risk']++,
                            default => $distribution['critical']++,
                        };
                    }
                });

            return $distribution;
        });
    }

    /**
     * Login frequency score (0-25 points).
     * Based on audit log entries in last 30 days.
     */
    private function loginFrequencyScore(User $user): int
    {
        $loginCount = 0;

        if (class_exists(\App\Models\AuditLog::class)) {
            $loginCount = \App\Models\AuditLog::where('user_id', $user->id)
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
     * Feature adoption score (0-25 points).
     * Based on breadth of feature usage.
     */
    private function featureAdoptionScore(User $user): int
    {
        $score = 0;

        // Has settings configured
        if ($user->settings()->count() > 0) {
            $score += 8;
        }

        // Has API tokens
        if ($user->tokens()->count() > 0) {
            $score += 8;
        }

        // Has webhook endpoints
        if ($user->webhookEndpoints()->count() > 0) {
            $score += 9;
        }

        return min($score, 25);
    }

    /**
     * Billing status score (0-25 points).
     * Uses direct DB queries to avoid lazy loading violations.
     */
    private function billingStatusScore(User $user): int
    {
        if (! DB::getSchemaBuilder()->hasTable('subscriptions')) {
            return 0;
        }

        $subscription = DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('type', 'default')
            ->first();

        if (! $subscription) {
            // Check if on trial (trial_ends_at on user)
            /** @var \Carbon\Carbon|null $trialEndsAt */
            $trialEndsAt = $user->trial_ends_at;
            if ($trialEndsAt !== null && $trialEndsAt->isFuture()) {
                return 20;
            }

            return 0;
        }

        return match ($subscription->stripe_status) {
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

        // Has settings configured (theme, timezone, etc.)
        if ($user->settings()->count() >= 2) {
            $score += 8;
        }

        // Has password set (not just OAuth)
        if ($user->hasPassword()) {
            $score += 7;
        }

        return min($score, 25);
    }
}
