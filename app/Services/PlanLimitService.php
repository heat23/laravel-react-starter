<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlanLimitService
{
    private const PLAN_CACHE_TTL = 10;

    public function __construct(
        private BillingService $billingService,
    ) {}

    /**
     * Check if trials are enabled in configuration.
     */
    public function isTrialEnabled(): bool
    {
        return (bool) config('plans.trial.enabled', false);
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
     * Get the user's current plan tier (cached).
     *
     * Resolves tier from: trial > active subscription (with grace period) > free.
     */
    public function getUserPlan(User $user): string
    {
        return Cache::remember(
            "user:{$user->id}:plan_tier",
            self::PLAN_CACHE_TTL,
            fn () => $this->resolveUserPlan($user)
        );
    }

    /**
     * Invalidate the cached plan tier for a user.
     * Call after subscription state changes (webhooks, cancel, resume, etc.).
     */
    public function invalidateUserPlanCache(User $user): void
    {
        Cache::forget("user:{$user->id}:plan_tier");
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

    /**
     * Resolve the user's plan tier without caching.
     */
    private function resolveUserPlan(User $user): string
    {
        // During trial, user has trial-tier access
        if ($this->isOnTrial($user)) {
            return config('plans.trial.tier', 'pro');
        }

        // Resolve tier from subscription's Stripe price
        if (config('features.billing.enabled')) {
            $subscription = $user->subscription('default');

            if (! $subscription) {
                return 'free';
            }

            // Past-due grace period enforcement
            if ($subscription->stripe_status === 'past_due') {
                $graceDays = config('plans.past_due_grace_days', 7);
                $graceExpiry = $subscription->updated_at->addDays($graceDays);

                if (now()->isAfter($graceExpiry)) {
                    Log::info('Past_due grace period expired, reverting to free tier', [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'grace_days' => $graceDays,
                    ]);

                    return 'free';
                }

                // Within grace period — resolve tier from price directly
                return $this->billingService->resolveTierFromPrice($subscription->stripe_price) ?? 'free';
            }

            // Active, trialing, or on grace period subscriptions
            if ($subscription->active()) {
                return $this->billingService->resolveTierFromPrice($subscription->stripe_price) ?? 'free';
            }
        }

        return 'free';
    }
}
