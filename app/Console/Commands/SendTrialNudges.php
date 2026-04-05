<?php

namespace App\Console\Commands;

use App\Enums\AnalyticsEvent;
use App\Jobs\DispatchAnalyticsEvent;
use App\Models\EmailSendLog;
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

        /** @var array<int, array{window_start: int, window_end: int}> $schedule */
        $schedule = config('email-sequences.trial_nudge');

        foreach ($schedule as $emailNumber => $window) {
            $windowStart = $window['window_start'] >= 0
                ? now()->addDays($window['window_start'])
                : now()->subDays(abs($window['window_start']));
            $windowEnd = $window['window_end'] >= 0
                ? now()->addDays($window['window_end'])
                : now()->subDays(abs($window['window_end']));
            $totalSent += $this->sendEmail($emailNumber, $windowStart, $windowEnd);
        }

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
                EmailSendLog::record($user->id, 'trial_nudge', $emailNumber);
                DispatchAnalyticsEvent::dispatch(
                    AnalyticsEvent::LIFECYCLE_EMAIL_SENT->value,
                    ['email_type' => 'trial_nudge', 'email_number' => $emailNumber],
                    $user->id,
                );
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
        return EmailSendLog::alreadySent($user->id, 'trial_nudge', $emailNumber);
    }
}
