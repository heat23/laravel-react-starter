<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\TrialNudgeNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTrialNudges extends Command
{
    protected $signature = 'emails:send-trial-nudges';

    protected $description = 'Send trial nudge emails based on trial_ends_at (7 days left, 3 days left, expired)';

    public function handle(): int
    {
        if (! config('features.billing.enabled')) {
            $this->info('Billing feature is disabled.');

            return self::SUCCESS;
        }

        $totalSent = 0;
        $totalSent += $this->sendEmail(1, now()->addDays(6), now()->addDays(8));   // 7 days left
        $totalSent += $this->sendEmail(2, now()->addDays(2), now()->addDays(4));   // 3 days left
        $totalSent += $this->sendEmail(3, now()->subDays(2), now());               // just expired

        $this->info("Sent {$totalSent} trial nudge emails.");

        return self::SUCCESS;
    }

    private function sendEmail(int $emailNumber, Carbon $windowStart, Carbon $windowEnd): int
    {
        $users = User::query()
            ->whereNotNull('trial_ends_at')
            ->whereNotNull('email_verified_at')
            ->where('trial_ends_at', '>=', $windowStart)
            ->where('trial_ends_at', '<=', $windowEnd)
            ->with('subscriptions')
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            if ($this->hasOptedOut($user)) {
                continue;
            }

            if ($this->hasActiveSubscription($user)) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            try {
                $user->notify(new TrialNudgeNotification($emailNumber, $user->trial_ends_at));
                $sent++;

                Log::info('Trial nudge email sent', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                    'trial_ends_at' => $user->trial_ends_at?->toDateString(),
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

    private function hasOptedOut(User $user): bool
    {
        $value = UserSetting::getValue($user->id, 'marketing_emails', true);

        return $value === false || $value === '0' || $value === 0;
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
