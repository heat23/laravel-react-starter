<?php

namespace App\Services;

use App\Enums\AuditEvent;
use App\Enums\LifecycleStage;
use App\Enums\PlanTier;
use App\Exceptions\ConcurrentOperationException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionItem;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException as StripeInvalidRequestException;

class BillingService
{
    /**
     * Lock timeout in seconds, read from config/billing.php.
     */
    private function lockTimeout(): int
    {
        return config('billing.lock_timeout', 35);
    }

    /**
     * Resolve which plan tier a user belongs to based on their active subscription's Stripe price.
     */
    public function resolveUserTier(User $user): PlanTier
    {
        $subscription = $user->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return PlanTier::Free;
        }

        return $this->resolveTierFromPrice($subscription->stripe_price) ?? PlanTier::Free;
    }

    /**
     * Create a Stripe Checkout session for a new subscriber.
     * Returns the hosted Checkout URL to redirect the user to.
     * Use this for first-time subscribers who have no stored payment method.
     */
    public function createCheckoutSession(
        User $user,
        string $priceId,
        int $quantity,
        string $successUrl,
        string $cancelUrl,
        ?string $coupon = null,
    ): string {
        $builder = $user->newSubscription('default', $priceId)->quantity($quantity);

        $checkoutOptions = [
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        if ($coupon) {
            $checkoutOptions['discounts'] = [['coupon' => $coupon]];
        }

        if (config('features.billing.tax_enabled')) {
            $checkoutOptions['automatic_tax'] = ['enabled' => true];
        }

        $checkout = $builder->checkout($checkoutOptions);

        return $checkout->url;
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
        return $this->withLock($this->lockKey('create', $user), function () use ($user, $priceId, $paymentMethod, $coupon, $quantity) {
            $subscription = DB::transaction(function () use ($user, $priceId, $paymentMethod, $coupon, $quantity) {
                $builder = $user->newSubscription('default', $priceId)->quantity($quantity);

                if ($coupon) {
                    $builder->withCoupon($coupon);
                }

                $subscriptionOptions = config('features.billing.tax_enabled')
                    ? ['automatic_tax' => ['enabled' => true]]
                    : [];

                if ($paymentMethod) {
                    return $builder->create($paymentMethod, [], $subscriptionOptions);
                }

                return $builder->create(null, [], $subscriptionOptions);
            });

            // After subscription creation, transition user to paying stage
            try {
                app(LifecycleService::class)->transition($user, LifecycleStage::PAYING, 'subscription_created');
            } catch (\Throwable $e) {
                Log::error('lifecycle_transition_failed', [
                    'user_id' => $user->id,
                    'target_stage' => LifecycleStage::PAYING->value,
                    'event' => 'subscription_created',
                    'exception' => $e->getMessage(),
                ]);
            }

            return $subscription;
        });
    }

    /**
     * Cancel the user's subscription.
     */
    public function cancelSubscription(User $user, bool $immediately = false): Subscription
    {
        return $this->withLock($this->lockKey('cancel', $user), function () use ($user, $immediately) {
            $subscription = DB::transaction(function () use ($user, $immediately) {
                $subscription = $user->subscription('default');
                $subscription->setRelation('owner', $user);
                $subscription->loadMissing('items');
                $subscription->items->each(fn ($item) => $item->setRelation('subscription', $subscription));

                if ($immediately) {
                    return $subscription->cancelNow();
                }

                return $subscription->cancel();
            });

            // Transition user to churned stage
            try {
                app(LifecycleService::class)->transition($user, LifecycleStage::CHURNED, 'subscription_cancelled');
            } catch (\Throwable $e) {
                Log::error('lifecycle_transition_failed', [
                    'user_id' => $user->id,
                    'target_stage' => LifecycleStage::CHURNED->value,
                    'event' => 'subscription_cancelled',
                    'exception' => $e->getMessage(),
                ]);
            }

            return $subscription;
        });
    }

    /**
     * Resume a canceled subscription during grace period.
     */
    public function resumeSubscription(User $user): Subscription
    {
        return $this->withLock($this->lockKey('resume', $user), function () use ($user) {
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
     * Transitions the user to EXPANSION stage when upgrading to a higher tier.
     */
    public function swapPlan(User $user, string $newPriceId, ?string $coupon = null): Subscription
    {
        return $this->withLock($this->lockKey('swap', $user), function () use ($user, $newPriceId, $coupon) {
            // Both tier resolutions and the swap happen inside the transaction to avoid
            // TOCTOU races with webhook-path stripe_price writes. resolveTierFromPrice is
            // config-only (no DB reads), but keeping it inside the transaction makes the
            // atomicity guarantee explicit and eliminates any future risk if tier resolution
            // ever gains a DB dependency.
            ['subscription' => $subscription, 'currentTier' => $currentTier, 'newTier' => $newTier] = DB::transaction(function () use ($user, $newPriceId, $coupon): array {
                $subscription = $user->subscription('default');
                $currentTier = $subscription ? $this->resolveTierFromPrice($subscription->stripe_price) : null;
                $newTier = $this->resolveTierFromPrice($newPriceId);

                $subscription->setRelation('owner', $user);
                $subscription->loadMissing('items');
                $subscription->items->each(fn ($item) => $item->setRelation('subscription', $subscription));

                $builder = $coupon ? $subscription->withCoupon($coupon) : $subscription;

                return [
                    'subscription' => $builder->swap($newPriceId),
                    'currentTier' => $currentTier,
                    'newTier' => $newTier,
                ];
            });

            // Transition to EXPANSION stage on plan upgrade (swap to strictly higher tier)
            if ($this->isUpgrade($currentTier, $newTier)) {
                try {
                    app(LifecycleService::class)->transition($user, LifecycleStage::EXPANSION, 'plan_upgraded');
                } catch (\Throwable $e) {
                    Log::error('lifecycle_transition_failed', [
                        'user_id' => $user->id,
                        'target_stage' => LifecycleStage::EXPANSION->value,
                        'event' => 'plan_upgraded',
                        'exception' => $e->getMessage(),
                    ]);
                }
            }

            return $subscription;
        });
    }

    /**
     * Returns true when the new tier is strictly higher than the current tier.
     */
    public function isUpgrade(?PlanTier $currentTier, ?PlanTier $newTier): bool
    {
        if ($currentTier === null || $newTier === null) {
            return false;
        }

        return $currentTier->canUpgradeTo($newTier);
    }

    /**
     * Update the subscription quantity (seat count for team/enterprise plans).
     */
    public function updateQuantity(User $user, int $quantity): Subscription
    {
        return $this->withLock($this->lockKey('quantity', $user), function () use ($user, $quantity) {
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
                'tier' => PlanTier::Free->value,
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
            'tier' => $this->resolveUserTier($user)->value,
            'status' => $subscription->stripe_status,
            'on_trial' => $subscription->onTrial(),
            'on_grace_period' => $subscription->onGracePeriod(),
            'quantity' => $subscription->quantity ?? 1,
            'ends_at' => $subscription->ends_at?->toISOString(),
            'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
        ];
    }

    /**
     * Validate a Stripe coupon code by retrieving it via the Stripe API.
     *
     * Returns null on success, or a user-facing error string on failure.
     */
    public function validateCouponCode(string $coupon): ?string
    {
        $cacheKey = 'coupon_valid_'.sha1($coupon);

        if (Cache::has($cacheKey)) {
            return null;
        }

        try {
            Cashier::stripe()->coupons->retrieve($coupon);
            Cache::put($cacheKey, true, 60);

            return null;
        } catch (StripeInvalidRequestException $e) {
            return 'The coupon code is invalid or has expired.';
        } catch (\Exception $e) {
            Log::warning('Coupon validation failed unexpectedly', [
                'coupon' => $coupon,
                'error' => $e->getMessage(),
            ]);

            return 'The coupon code is invalid or has expired.';
        }
    }

    /**
     * Validate the seat count for a given tier.
     */
    public function validateSeatCount(PlanTier $tier, int $quantity): ?string
    {
        $tierConfig = config("plans.{$tier->value}");

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
    public function resolveTierFromPrice(string $priceId): ?PlanTier
    {
        $tier = PlanTier::tryFromStripePriceId($priceId);

        if ($tier === null) {
            Log::warning('Unknown Stripe price ID encountered during tier resolution', [
                'price_id' => $priceId,
            ]);
        }

        return $tier;
    }

    /**
     * Preview the proration cost for swapping to a new price using Stripe's upcoming invoice API.
     *
     * @return array{amount_due: int, next_billing_date: string|null}
     *
     * @throws \InvalidArgumentException When $newPriceId is not a recognised plan price.
     * @throws \RuntimeException On Stripe API failure.
     */
    public function previewSwapProration(User $user, string $newPriceId): array
    {
        if (PlanTier::tryFromStripePriceId($newPriceId) === null) {
            throw new \InvalidArgumentException("Invalid price ID: {$newPriceId}");
        }

        $subscription = $user->subscription('default');
        $subscription->setRelation('owner', $user);
        $subscription->loadMissing('items');
        /** @var SubscriptionItem $currentItem */
        $currentItem = $subscription->items->first();

        try {
            $stripeInvoice = Cashier::stripe()->invoices->createPreview([
                'customer' => $user->stripe_id,
                'subscription' => $subscription->stripe_id,
                'subscription_items' => [
                    ['id' => $currentItem->stripe_id, 'price' => $newPriceId],
                ],
                'subscription_proration_behavior' => 'always_invoice',
            ]);
        } catch (StripeInvalidRequestException $e) {
            Log::warning('Failed to preview swap proration', [
                'user_id' => $user->id,
                'error' => 'Invalid request',
            ]);
            throw new \RuntimeException('Unable to calculate proration cost. Please try again.');
        } catch (\Exception $e) {
            Log::error('Unexpected error previewing swap proration', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('An unexpected error occurred. Please contact support.');
        }

        return [
            'amount_due' => $stripeInvoice->amount_due,
            'next_billing_date' => $stripeInvoice->next_payment_attempt
                ? date('Y-m-d', $stripeInvoice->next_payment_attempt)
                : null,
        ];
    }

    /**
     * Apply a retention coupon to the user's active subscription.
     *
     * Follows the same Redis-lock + eager-load pattern as other mutation methods.
     * Audit log is written on the success path only, outside the DB transaction.
     *
     * @throws ConcurrentOperationException When a concurrent subscription operation is in progress.
     * @throws \DomainException When the user has no active subscription.
     * @throws ApiErrorException When Stripe rejects the coupon (e.g. unknown coupon ID).
     */
    public function applyRetentionCoupon(User $user, string $couponId): void
    {
        $this->withLock($this->lockKey('coupon', $user), function () use ($user, $couponId) {
            $stripeSubscriptionId = DB::transaction(function () use ($user, $couponId) {
                $subscription = $user->subscription('default');

                if (! $subscription || ! $subscription->active()) {
                    throw new \DomainException('No active subscription found.');
                }

                $subscription->setRelation('owner', $user);
                $subscription->loadMissing('items');
                $subscription->items->each(fn ($item) => $item->setRelation('subscription', $subscription));

                $subscription->applyCoupon($couponId);

                return $subscription->stripe_id;
            });

            app(AuditService::class)->log(AuditEvent::BILLING_RETENTION_COUPON_APPLIED, [
                'user_id' => $user->id,
                'coupon_id' => $couponId,
                'subscription_id' => $stripeSubscriptionId,
                'churn_save_context' => true,
            ]);
        });
    }

    /**
     * Return the Cache lock key for a given subscription operation and user.
     *
     * Exposed as public so tests can acquire the same lock to simulate concurrency
     * without duplicating key-construction logic.
     */
    public function lockKey(string $operation, User $user): string
    {
        return "subscription:{$operation}:{$user->id}";
    }

    /**
     * Execute a callback within a Redis lock to prevent concurrent operations.
     *
     * @throws ConcurrentOperationException
     */
    private function withLock(string $key, callable $callback): mixed
    {
        $timeout = $this->lockTimeout();
        $lock = Cache::lock($key, $timeout);

        if (! $lock->get()) {
            Log::warning('billing_lock_failed', [
                'key' => $key,
                'timeout' => $timeout,
                'user_id' => auth()->id(),
            ]);
            throw new ConcurrentOperationException('A subscription operation is already in progress. Please try again.');
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
