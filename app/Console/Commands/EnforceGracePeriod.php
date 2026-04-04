<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnforceGracePeriod extends Command
{
    protected $signature = 'billing:enforce-grace-period';

    protected $description = 'Cancel past-due subscriptions that have exceeded the grace period and downgrade users to free tier';

    public function handle(BillingService $billingService): int
    {
        if (! config('features.billing.enabled')) {
            $this->info('Billing feature is disabled.');

            return self::SUCCESS;
        }

        $gracePeriodDays = (int) config('billing.grace_period_days', 7);
        $cutoff = now()->subDays($gracePeriodDays);

        $subscriptions = Subscription::where('stripe_status', 'past_due')
            ->whereNotNull('past_due_since')
            ->where('past_due_since', '<=', $cutoff)
            ->with('user')
            ->get();

        $cancelled = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;

            if (! $user instanceof User) {
                continue;
            }

            try {
                $billingService->cancelSubscription($user, immediately: true);
                $cancelled++;

                Log::info('Grace period exceeded — subscription cancelled', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'past_due_since' => $subscription->past_due_since?->toISOString(),
                    'grace_period_days' => $gracePeriodDays,
                ]);
            } catch (\Throwable $e) {
                $failed++;

                Log::error('Failed to cancel past-due subscription after grace period', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Grace period enforcement complete: {$cancelled} cancelled, {$failed} failed.");

        return self::SUCCESS;
    }
}
