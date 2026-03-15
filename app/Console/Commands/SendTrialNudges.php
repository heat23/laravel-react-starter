<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TrialNudgeNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTrialNudges extends Command
{
    protected $signature = 'emails:send-trial-nudges';

    protected $description = 'Send trial nudge emails to users approaching trial end (day 7, 12, 14)';

    /** @var array<int, array{days: int, maxDays: int}> */
    private const EMAIL_SCHEDULE = [
        1 => ['days' => 7, 'maxDays' => 9],
        2 => ['days' => 12, 'maxDays' => 13],
        3 => ['days' => 14, 'maxDays' => 16],
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

        $this->info("Sent {$totalSent} trial nudge emails.");

        return self::SUCCESS;
    }

    private function sendEmailNumber(int $emailNumber, int $minDays, int $maxDays): int
    {
        $users = User::query()
            ->whereNotNull('trial_ends_at')
            ->whereNotNull('email_verified_at')
            ->where('created_at', '<=', now()->subDays($minDays))
            ->where('created_at', '>', now()->subDays($maxDays))
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            if ($this->hasActiveSubscription($user)) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            try {
                $user->notify(new TrialNudgeNotification($emailNumber));
                $sent++;

                Log::info('Trial nudge email sent', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send trial nudge email', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function hasActiveSubscription(User $user): bool
    {
        return $user->subscribed('default');
    }

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        return $user->notifications()
            ->where('type', TrialNudgeNotification::class)
            ->where('data', 'like', '%"email_number":'.$emailNumber.'%')
            ->exists();
    }
}
