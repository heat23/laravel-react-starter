<?php

namespace App\Jobs;

use App\Services\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CancelOrphanedStripeSubscription implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly string $stripeCustomerId,
        private readonly ?int $userId = null,
    ) {}

    public function handle(BillingService $billing): void
    {
        $billing->cancelOrphanedStripeSubscription($this->stripeCustomerId, $this->userId);
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
