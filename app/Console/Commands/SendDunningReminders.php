<?php

namespace App\Console\Commands;

use App\Models\EmailSendLog;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\DunningReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
            ->whereNotNull('past_due_since')
            ->where('past_due_since', '<=', now()->subDays($minDays))
            ->where('past_due_since', '>', now()->subDays($maxDays))
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            /** @var User|null $user */
            $user = $subscription->user;

            if (! $user) {
                continue;
            }

            if ($this->hasOptedOut($user)) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            $planName = $this->resolvePlanName($subscription);

            try {
                $user->notify(new DunningReminderNotification($emailNumber, $planName));
                EmailSendLog::record($user->id, 'dunning_reminder', $emailNumber);
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

    private function hasOptedOut(User $user): bool
    {
        $value = UserSetting::getValue($user->id, 'marketing_emails', true);

        return $value === false || $value === '0' || $value === 0;
    }

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        return EmailSendLog::alreadySent($user->id, 'dunning_reminder', $emailNumber);
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
