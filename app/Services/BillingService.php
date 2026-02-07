<?php

namespace App\Services;

use App\Exceptions\ConcurrentOperationException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class BillingService
{
    /**
     * Lock timeout in seconds: 30s Stripe API + 5s buffer.
     */
    private const LOCK_TIMEOUT = 35;

    /**
     * Resolve which plan tier a user belongs to based on their active subscription's Stripe price.
     */
    public function resolveUserTier(User $user): string
    {
        $subscription = $user->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return 'free';
        }

        return $this->resolveTierFromPrice($subscription->stripe_price);
    }

    /**
     * Create a new subscription for the user.
     */
    public function createSubscription(
        User $user,
        string $priceId,
        ?string $paymentMethod = null,
        ?string $coupon = null,
        int $quantity = 1,
    ): Subscription {
        return $this->withLock("subscription:create:{$user->id}", function () use ($user, $priceId, $paymentMethod, $coupon, $quantity) {
            return DB::transaction(function () use ($user, $priceId, $paymentMethod, $coupon, $quantity) {
                $builder = $user->newSubscription('default', $priceId)->quantity($quantity);

                if ($coupon) {
                    $builder->withCoupon($coupon);
                }

                if ($paymentMethod) {
                    return $builder->create($paymentMethod);
                }

                return $builder->create();
            });
        });
    }

    /**
     * Cancel the user's subscription.
     */
    public function cancelSubscription(User $user, bool $immediately = false): Subscription
    {
        return $this->withLock("subscription:cancel:{$user->id}", function () use ($user, $immediately) {
            return DB::transaction(function () use ($user, $immediately) {
                $subscription = $user->subscription('default');
                $subscription->setRelation('owner', $user);
                $subscription->loadMissing('items');
                $subscription->items->each(fn ($item) => $item->setRelation('subscription', $subscription));

                if ($immediately) {
                    return $subscription->cancelNow();
                }

                return $subscription->cancel();
            });
        });
    }

    /**
     * Resume a canceled subscription during grace period.
     */
    public function resumeSubscription(User $user): Subscription
    {
        return $this->withLock("subscription:resume:{$user->id}", function () use ($user) {
            return DB::transaction(function () use ($user) {
                $subscription = $user->subscription('default');
                $subscription->setRelation('owner', $user);
                $subscription->loadMissing('items');
                $subscription->items->each(fn ($item) => $item->setRelation('subscription', $subscription));

                return $subscription->resume();
            });
        });
    }

    /**
     * Swap the subscription to a new plan/price.
     */
    public function swapPlan(User $user, string $newPriceId): Subscription
    {
        return $this->withLock("subscription:swap:{$user->id}", function () use ($user, $newPriceId) {
            return DB::transaction(function () use ($user, $newPriceId) {
                $subscription = $user->subscription('default');
                $subscription->setRelation('owner', $user);
                $subscription->loadMissing('items');
                $subscription->items->each(fn ($item) => $item->setRelation('subscription', $subscription));

                return $subscription->swap($newPriceId);
            });
        });
    }

    /**
     * Update the subscription quantity (seat count for team/enterprise plans).
     */
    public function updateQuantity(User $user, int $quantity): Subscription
    {
        return $this->withLock("subscription:quantity:{$user->id}", function () use ($user, $quantity) {
            return DB::transaction(function () use ($user, $quantity) {
                $subscription = $user->subscription('default');
                $subscription->setRelation('owner', $user);
                $subscription->loadMissing('items');
                $subscription->items->each(fn ($item) => $item->setRelation('subscription', $subscription));

                return $subscription->updateQuantity($quantity);
            });
        });
    }

    /**
     * Update the user's default payment method.
     */
    public function updatePaymentMethod(User $user, string $paymentMethodId): void
    {
        $user->updateDefaultPaymentMethod($paymentMethodId);
    }

    /**
     * Get the Stripe billing portal URL.
     */
    public function getBillingPortalUrl(User $user, string $returnUrl): string
    {
        return $user->billingPortalUrl($returnUrl);
    }

    /**
     * Get comprehensive subscription status for the user.
     */
    public function getSubscriptionStatus(User $user): array
    {
        $subscription = $user->subscription('default');

        if (! $subscription) {
            return [
                'subscribed' => false,
                'tier' => 'free',
                'status' => null,
                'on_trial' => $user->trial_ends_at?->isFuture() ?? false,
                'on_grace_period' => false,
                'quantity' => 1,
                'ends_at' => null,
                'trial_ends_at' => $user->trial_ends_at?->toISOString(),
            ];
        }

        return [
            'subscribed' => $subscription->active(),
            'tier' => $this->resolveUserTier($user),
            'status' => $subscription->stripe_status,
            'on_trial' => $subscription->onTrial(),
            'on_grace_period' => $subscription->onGracePeriod(),
            'quantity' => $subscription->quantity ?? 1,
            'ends_at' => $subscription->ends_at?->toISOString(),
            'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
        ];
    }

    /**
     * Validate the seat count for a given tier.
     */
    public function validateSeatCount(string $tier, int $quantity): ?string
    {
        $tierConfig = config("plans.{$tier}");

        if (! $tierConfig) {
            return 'Invalid plan tier.';
        }

        if (! ($tierConfig['per_seat'] ?? false)) {
            if ($quantity !== 1) {
                return 'This plan does not support per-seat billing.';
            }

            return null;
        }

        $minSeats = $tierConfig['min_seats'] ?? 1;
        if ($quantity < $minSeats) {
            return "This plan requires a minimum of {$minSeats} seats.";
        }

        $maxSeats = $tierConfig['limits']['seats'] ?? null;
        if ($maxSeats !== null && $quantity > $maxSeats) {
            return "This plan supports a maximum of {$maxSeats} seats.";
        }

        return null;
    }

    /**
     * Resolve the tier for a given Stripe price ID.
     */
    public function resolveTierFromPrice(string $priceId): string
    {
        foreach (['enterprise', 'team', 'pro'] as $tier) {
            $monthlyPrice = config("plans.{$tier}.stripe_price_monthly");
            $annualPrice = config("plans.{$tier}.stripe_price_annual");

            if ($priceId === $monthlyPrice || $priceId === $annualPrice) {
                return $tier;
            }
        }

        Log::warning('Unknown Stripe price ID encountered during tier resolution', [
            'price_id' => $priceId,
        ]);

        return 'free';
    }

    /**
     * Execute a callback within a Redis lock to prevent concurrent operations.
     *
     * @throws ConcurrentOperationException
     */
    private function withLock(string $key, callable $callback): mixed
    {
        $lock = Cache::lock($key, self::LOCK_TIMEOUT);

        if (! $lock->get()) {
            throw new ConcurrentOperationException;
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
