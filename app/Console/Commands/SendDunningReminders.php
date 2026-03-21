<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DunningReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class SendDunningReminders extends Command
{
    protected $signature = 'notifications:send-dunning';

    protected $description = 'Send dunning reminder emails to users with past-due subscriptions';

    /** @var array<int, array{days: int, maxDays: int}> */
    private const EMAIL_SCHEDULE = [
        1 => ['days' => 3, 'maxDays' => 5],
        2 => ['days' => 7, 'maxDays' => 10],
        3 => ['days' => 12, 'maxDays' => 15],
    ];

    public function handle(): int
    {
        if (! config('features.billing.enabled')) {
            $this->info('Billing feature is disabled.');

            return self::SUCCESS;
        }

        $totalSent = 0;

        foreach (self::EMAIL_SCHEDULE as $emailNumber => $schedule) {
            $sent = $this->sendEmailNumber($emailNumber, $schedule['days'], $schedule['maxDays']);
            $totalSent += $sent;
        }

        $this->info("Sent {$totalSent} dunning reminders.");

        return self::SUCCESS;
    }

    private function sendEmailNumber(int $emailNumber, int $minDays, int $maxDays): int
    {
        $subscriptions = Subscription::where('stripe_status', 'past_due')
            ->where('updated_at', '<=', now()->subDays($minDays))
            ->where('updated_at', '>', now()->subDays($maxDays))
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            /** @var User|null $user */
            $user = $subscription->user;

            if (! $user) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            $planName = $this->resolvePlanName($subscription);

            try {
                $user->notify(new DunningReminderNotification($emailNumber, $planName));
                $sent++;

                Log::info('Dunning reminder sent', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'email_number' => $emailNumber,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send dunning reminder', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'email_number' => $emailNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        return $user->notifications()
            ->where('type', DunningReminderNotification::class)
            ->where('data', 'like', '%"email_number":'.$emailNumber.'%')
            ->exists();
    }

    private function resolvePlanName(Subscription $subscription): string
    {
        $plans = config('plans.tiers', []);

        foreach ($plans as $plan) {
            if (isset($plan['stripe_price_id']) && $plan['stripe_price_id'] === $subscription->stripe_price) {
                return $plan['name'] ?? 'your plan';
            }
        }

        return 'your plan';
    }
}
