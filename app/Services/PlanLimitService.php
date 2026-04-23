<?php

namespace App\Services;

use App\Enums\AuditEvent;
use App\Enums\PlanTier;
use App\Events\PqlThresholdReached;
use App\Models\User;
use App\Support\Billing\PlanLimitResult;
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
        $trialDays = (int) config('plans.trial.days', 14);
        $tier = config('plans.trial.tier', PlanTier::Pro->value);
        $trialEndsAt = Carbon::now()->addDays($trialDays);

        // Atomic write: only set if trial hasn't already been started.
        // Uses a WHERE NULL guard so concurrent calls are safe without a separate lock.
        $affected = User::where('id', $user->id)
            ->whereNull('trial_ends_at')
            ->update(['trial_ends_at' => $trialEndsAt]);

        if ($affected === 0) {
            // Another process already started the trial — skip audit to avoid duplicates.
            return;
        }

        $user->trial_ends_at = $trialEndsAt;

        $auditService = app(AuditService::class);
        $auditService->log(AuditEvent::TRIAL_STARTED, [
            'user_id' => $user->id,
            'tier' => $tier,
            'trial_days' => $trialDays,
            'trial_ends_at' => $trialEndsAt->toISOString(),
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
    public function getUserPlan(User $user): PlanTier
    {
        return Cache::remember(
            "user:{$user->id}:plan_tier",
            self::PLAN_CACHE_TTL,
            fn () => $this->resolveUserPlan($user)
        );
    }

    /**
     * Invalidate the cached plan tier and limit warnings for a user.
     * Call after subscription state changes (webhooks, cancel, resume, etc.).
     */
    public function invalidateUserPlanCache(User $user): void
    {
        Cache::forget("user:{$user->id}:plan_tier");
        Cache::forget("user:{$user->id}:limit_warnings");
    }

    /**
     * Get a specific limit for the user based on their plan.
     */
    public function getLimit(User $user, string $limitKey): ?int
    {
        $plan = $this->getUserPlan($user);

        return config("plans.{$plan->value}.limits.{$limitKey}");
    }

    /**
     * Check if user can perform an action based on limits.
     * Emits PQL threshold events at 50%, 80%, and 100% usage.
     *
     * Returns a PlanLimitResult DTO. Callers (controllers) are responsible
     * for any session flashing — this service no longer accesses the session.
     *
     * @param  int  $currentCount  Current usage count
     */
    public function canPerform(User $user, string $limitKey, int $currentCount): PlanLimitResult
    {
        $limit = $this->getLimit($user, $limitKey);

        // null means unlimited
        if ($limit === null) {
            return new PlanLimitResult(allowed: true);
        }

        $this->checkThresholds($user, $limitKey, $currentCount, $limit);

        if ($currentCount < $limit) {
            return new PlanLimitResult(allowed: true);
        }

        $nextTier = $this->getNextTier($this->getUserPlan($user));

        return new PlanLimitResult(
            allowed: false,
            reason: 'limit_exceeded',
            upgradeTier: $nextTier,
            userMessage: $nextTier !== null
                ? "You have reached the {$limitKey} limit for your plan. Upgrade to {$nextTier->label()} to create more."
                : "You have reached the {$limitKey} limit for your plan.",
        );
    }

    /**
     * Get the current usage percentage for a limit key (0–100), or null if unlimited.
     */
    public function getUsagePercent(User $user, string $limitKey): ?int
    {
        $limit = $this->getLimit($user, $limitKey);

        if ($limit === null || $limit <= 0) {
            return null;
        }

        // Determine current count based on limit key
        $currentCount = match ($limitKey) {
            'api_tokens' => $user->tokens()->count(),
            'webhook_endpoints' => $user->webhookEndpoints()->count(),
            default => 0,
        };

        return (int) min(100, round(($currentCount / $limit) * 100));
    }

    /**
     * Emit PQL (Product Qualified Lead) threshold events when usage approaches limits.
     */
    private function checkThresholds(User $user, string $limitKey, int $currentCount, int $limit): void
    {
        if ($limit <= 0) {
            return;
        }

        $percentage = ($currentCount / $limit) * 100;

        $thresholds = [100, 80, 50];

        foreach ($thresholds as $threshold) {
            if ($percentage >= $threshold) {
                // Guard: fire at most once per user per limit per threshold per day.
                $cacheKey = "pql:{$user->id}:{$limitKey}:threshold_{$threshold}";
                if (Cache::has($cacheKey)) {
                    break;
                }
                Cache::put($cacheKey, true, now()->addHours(24));

                $auditService = app(AuditService::class);
                $analyticsEvent = match ($threshold) {
                    50 => AuditEvent::LIMIT_THRESHOLD_50,
                    80 => AuditEvent::LIMIT_THRESHOLD_80,
                    100 => AuditEvent::LIMIT_THRESHOLD_100,
                };
                $auditService->log($analyticsEvent, [
                    'user_id' => $user->id,
                    'limit_key' => $limitKey,
                    'current' => $currentCount,
                    'max' => $limit,
                ]);

                PqlThresholdReached::dispatch($user, $limitKey, $percentage, $threshold);

                break;
            }
        }
    }

    /**
     * Get the next upgrade tier above the given plan.
     *
     * Returns null when the user is already on the top tier (no upgrade available),
     * which the caller should use to suppress the upgrade-prompt flash entirely.
     */
    public function getNextTier(PlanTier $currentPlan): ?PlanTier
    {
        $cases = PlanTier::cases();
        $index = array_search($currentPlan, $cases, strict: true);

        if ($index === false || $index === array_key_last($cases)) {
            return null;
        }

        return $cases[$index + 1];
    }

    /**
     * Resolve the user's plan tier without caching.
     */
    private function resolveUserPlan(User $user): PlanTier
    {
        // During trial, user has trial-tier access
        if ($this->isOnTrial($user)) {
            return PlanTier::tryFrom(config('plans.trial.tier', PlanTier::Pro->value)) ?? PlanTier::Pro;
        }

        // Resolve tier from subscription's Stripe price
        if (config('features.billing.enabled')) {
            $subscription = $user->subscription('default');

            if (! $subscription) {
                return PlanTier::Free;
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

                    return PlanTier::Free;
                }

                // Within grace period — resolve tier from price directly
                return $this->billingService->resolveTierFromPrice($subscription->stripe_price) ?? PlanTier::Free;
            }

            // Active, trialing, or on grace period subscriptions
            if ($subscription->active()) {
                return $this->billingService->resolveTierFromPrice($subscription->stripe_price) ?? PlanTier::Free;
            }
        }

        return PlanTier::Free;
    }
}
