<?php

namespace App\Console\Commands;

use App\Models\EmailSendLog;
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

        $daysBeforeExpiry = (int) config('email-sequences.trial_ending.days_before_expiry', 3);

        $users = User::query()
            ->whereNotNull('trial_ends_at')
            ->whereNotNull('email_verified_at')
            ->where('trial_ends_at', '>', now())
            ->where('trial_ends_at', '<=', now()->addDays($daysBeforeExpiry))
            ->with('subscriptions')
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            // Skip already-paying users
            if ($user->subscribed('default')) {
                continue;
            }

            if ($this->hasOptedOut($user)) {
                continue;
            }

            // Dedup: skip if already sent this reminder
            if (EmailSendLog::alreadySent($user->id, 'trial_ending_reminder', 1)) {
                continue;
            }

            $daysRemaining = max(0, (int) ceil(now()->diffInSeconds($user->trial_ends_at) / 86400));

            try {
                $user->notify(new TrialEndingNotification($daysRemaining));
                EmailSendLog::record($user->id, 'trial_ending_reminder', 1);
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

    private function hasOptedOut(User $user): bool
    {
        $value = UserSetting::getValue($user->id, 'marketing_emails', true);

        return $value === false || $value === '0' || $value === 0;
    }
}
