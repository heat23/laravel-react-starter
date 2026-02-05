<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Plan Limit Service
 *
 * Simplified service for managing subscription plan limits and trials.
 * Works with config/plans.php and config/features.php settings.
 *
 * For full subscription management, integrate Laravel Cashier.
 */
class PlanLimitService
{
    /**
     * Check if trials are enabled in configuration.
     */
    public function isTrialEnabled(): bool
    {
        return config('plans.trial.enabled', false)
            || config('features.billing.trial_enabled', false);
    }

    /**
     * Start a trial period for a user.
     */
    public function startTrial(User $user): void
    {
        $trialDays = config('plans.trial.days', 14);

        $user->update([
            'trial_ends_at' => Carbon::now()->addDays($trialDays),
        ]);
    }

    /**
     * Check if user is currently in trial period.
     */
    public function isOnTrial(User $user): bool
    {
        if (! $user->trial_ends_at) {
            return false;
        }

        return $user->trial_ends_at->isFuture();
    }

    /**
     * Get the number of days remaining in trial.
     */
    public function trialDaysRemaining(User $user): int
    {
        if (! $this->isOnTrial($user)) {
            return 0;
        }

        return (int) now()->diffInDays($user->trial_ends_at, absolute: false);
    }

    /**
     * Get the user's current plan tier.
     *
     * Returns 'pro' during trial, otherwise 'free'.
     * When billing feature is enabled, integrate Laravel Cashier here.
     */
    public function getUserPlan(User $user): string
    {
        // During trial, user has pro access
        if ($this->isOnTrial($user)) {
            return config('plans.trial.tier', 'pro');
        }

        // Cashier integration point:
        // if (config('features.billing.enabled') && $user->subscribed('default')) {
        //     return 'pro';
        // }

        return 'free';
    }

    /**
     * Get a specific limit for the user based on their plan.
     */
    public function getLimit(User $user, string $limitKey): ?int
    {
        $plan = $this->getUserPlan($user);

        return config("plans.{$plan}.limits.{$limitKey}");
    }

    /**
     * Check if user can perform an action based on limits.
     *
     * @param  int  $currentCount  Current usage count
     * @return bool True if under limit, false if at/over limit
     */
    public function canPerform(User $user, string $limitKey, int $currentCount): bool
    {
        $limit = $this->getLimit($user, $limitKey);

        // null means unlimited
        if ($limit === null) {
            return true;
        }

        return $currentCount < $limit;
    }
}
