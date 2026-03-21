<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\TrialEndingNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTrialEndingReminders extends Command
{
    protected $signature = 'trial:send-reminders';

    protected $description = 'Send trial-ending reminder emails to users whose trials expire within 3 days';

    public function handle(): int
    {
        if (! config('features.billing.enabled')) {
            $this->info('Billing feature is disabled.');

            return self::SUCCESS;
        }

        $users = User::query()
            ->whereNotNull('trial_ends_at')
            ->whereNotNull('email_verified_at')
            ->where('trial_ends_at', '>', now())
            ->where('trial_ends_at', '<=', now()->addDays(3))
            ->with('subscriptions')
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            // Skip already-paying users
            if ($user->subscribed('default')) {
                continue;
            }

            // Dedup: skip if already notified today
            $settingKey = 'trial_reminder_sent_at';
            $lastSent = UserSetting::getValue($user->id, $settingKey);
            if ($lastSent && now()->parse($lastSent)->isToday()) {
                continue;
            }

            $daysRemaining = max(0, (int) now()->diffInDays($user->trial_ends_at, false));

            try {
                $user->notify(new TrialEndingNotification($daysRemaining));
                UserSetting::setValue($user->id, $settingKey, now()->toISOString());
                $sent++;

                Log::info('trial_ending_reminder_sent', [
                    'user_id' => $user->id,
                    'days_remaining' => $daysRemaining,
                ]);
            } catch (\Exception $e) {
                Log::error('trial_ending_reminder_failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} trial-ending reminder emails.");

        return self::SUCCESS;
    }
}
