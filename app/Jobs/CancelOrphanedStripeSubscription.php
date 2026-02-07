<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;

class CancelOrphanedStripeSubscription implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly string $stripeCustomerId,
        private readonly ?int $userId = null,
    ) {}

    public function handle(): void
    {
        $stripe = Cashier::stripe();

        $subscriptions = $stripe->subscriptions->all([
            'customer' => $this->stripeCustomerId,
            'status' => 'active',
        ]);

        foreach ($subscriptions->data as $subscription) {
            $stripe->subscriptions->cancel($subscription->id);

            Log::info('Canceled orphaned Stripe subscription', [
                'stripe_customer_id' => $this->stripeCustomerId,
                'stripe_subscription_id' => $subscription->id,
                'user_id' => $this->userId,
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Failed to cancel orphaned Stripe subscription after all retries', [
            'stripe_customer_id' => $this->stripeCustomerId,
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);
    }
}
