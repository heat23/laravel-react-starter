<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\OnboardingReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOnboardingReminders extends Command
{
    protected $signature = 'notifications:send-onboarding';

    protected $description = 'Send onboarding reminder emails to users who haven\'t completed setup';

    /** @var array<int, array{days: int, maxDays: int}> */
    private const EMAIL_SCHEDULE = [
        1 => ['days' => 1, 'maxDays' => 2],
        2 => ['days' => 3, 'maxDays' => 5],
        3 => ['days' => 7, 'maxDays' => 10],
    ];

    public function handle(): int
    {
        if (! config('features.onboarding.enabled')) {
            $this->info('Onboarding feature is disabled.');

            return self::SUCCESS;
        }

        $totalSent = 0;

        foreach (self::EMAIL_SCHEDULE as $emailNumber => $schedule) {
            $sent = $this->sendEmailNumber($emailNumber, $schedule['days'], $schedule['maxDays']);
            $totalSent += $sent;
        }

        $this->info("Sent {$totalSent} onboarding reminders.");

        return self::SUCCESS;
    }

    private function sendEmailNumber(int $emailNumber, int $minDays, int $maxDays): int
    {
        $users = User::query()
            ->where('created_at', '<=', now()->subDays($minDays))
            ->where('created_at', '>', now()->subDays($maxDays))
            ->whereNotNull('email_verified_at')
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            if ($this->hasCompletedOnboarding($user)) {
                continue;
            }

            if ($emailNumber === 3 && $this->hasRecentActivity($user)) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            try {
                $user->notify(new OnboardingReminderNotification($emailNumber));
                $sent++;

                Log::info('Onboarding reminder sent', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send onboarding reminder', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function hasCompletedOnboarding(User $user): bool
    {
        return (bool) $user->getSetting('onboarding_completed');
    }

    private function hasRecentActivity(User $user): bool
    {
        if (! class_exists(\App\Models\AuditLog::class)) {
            return false;
        }

        return \App\Models\AuditLog::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDays(3))
            ->exists();
    }

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        return $user->notifications()
            ->where('type', OnboardingReminderNotification::class)
            ->where('data', 'like', '%"email_number":'.$emailNumber.'%')
            ->exists();
    }
}
