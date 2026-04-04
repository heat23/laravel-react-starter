<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\WinBackNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class SendWinBackEmails extends Command
{
    protected $signature = 'emails:send-win-back';

    protected $description = 'Send win-back emails to recently canceled subscribers';

    /** @var array<int, array{days: int, maxDays: int}> */
    private const EMAIL_SCHEDULE = [
        1 => ['days' => 3, 'maxDays' => 5],
        2 => ['days' => 14, 'maxDays' => 17],
        3 => ['days' => 30, 'maxDays' => 33],
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

        $this->info("Sent {$totalSent} win-back emails.");

        return self::SUCCESS;
    }

    private function sendEmailNumber(int $emailNumber, int $minDays, int $maxDays): int
    {
        $subscriptions = Subscription::whereIn('stripe_status', ['canceled', 'incomplete_expired'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->subDays($minDays))
            ->where('ends_at', '>', now()->subDays($maxDays))
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            /** @var User|null $user */
            $user = $subscription->user;

            if (! $user) {
                continue;
            }

            // Skip users who have reactivated
            $user->loadMissing('subscriptions');
            if ($user->subscribed('default')) {
                continue;
            }

            if ($this->hasOptedOut($user)) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            try {
                $user->notify(new WinBackNotification($emailNumber));
                $sent++;

                Log::info('Win-back email sent', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'email_number' => $emailNumber,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send win-back email', [
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
        // Use a non-digit anchor after the number to avoid matching e.g. :1 inside :10 or :11.
        // JSON serializes the last key as ":N}" and intermediate keys as ":N,".
        $pattern = '%"email_number":'.$emailNumber.'[^0-9]%';

        return $user->notifications()
            ->where('type', WinBackNotification::class)
            ->where('data', 'like', $pattern)
            ->exists();
    }
}
