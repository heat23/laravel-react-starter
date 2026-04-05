<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\EmailSendLog;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\OnboardingReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOnboardingReminders extends Command
{
    protected $signature = 'notifications:send-onboarding';

    protected $description = 'Send onboarding reminder emails to users who haven\'t completed setup';

    public function handle(): int
    {
        // Onboarding reminder emails are sent regardless of whether the wizard UI is enabled.
        // When the feature is disabled we still send but use the dashboard URL as the CTA.
        $onboardingEnabled = (bool) config('features.onboarding.enabled');

        $totalSent = 0;

        /** @var array<int, array{days: int, max_days: int}> $emailSchedule */
        $emailSchedule = config('email-sequences.onboarding');

        foreach ($emailSchedule as $emailNumber => $schedule) {
            $sent = $this->sendEmailNumber($emailNumber, $schedule['days'], $schedule['max_days'], $onboardingEnabled);
            $totalSent += $sent;
        }

        $this->info("Sent {$totalSent} onboarding reminders.");

        return self::SUCCESS;
    }

    private function sendEmailNumber(int $emailNumber, int $minDays, int $maxDays, bool $onboardingEnabled = true): int
    {
        $users = User::query()
            ->where('created_at', '<=', now()->subDays($minDays))
            ->where('created_at', '>', now()->subDays($maxDays))
            ->whereNotNull('email_verified_at')
            ->get();

        $sent = 0;

        // CTA URL: use onboarding route only when the wizard UI is enabled
        $ctaUrl = $onboardingEnabled ? route('onboarding') : route('dashboard');

        foreach ($users as $user) {
            if ($this->hasOptedOut($user)) {
                continue;
            }

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
                $user->notify(new OnboardingReminderNotification($emailNumber, $ctaUrl));
                EmailSendLog::record($user->id, 'onboarding_reminder', $emailNumber);
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

    private function hasOptedOut(User $user): bool
    {
        $value = UserSetting::getValue($user->id, 'marketing_emails', true);

        return $value === false || $value === '0' || $value === 0;
    }

    private function hasCompletedOnboarding(User $user): bool
    {
        if (! class_exists(UserSetting::class)) {
            return false;
        }

        $setting = UserSetting::where('user_id', $user->id)
            ->where('key', 'onboarding_completed')
            ->first();

        if (! $setting) {
            return false;
        }

        $value = json_decode($setting->value, true);

        return json_last_error() === JSON_ERROR_NONE ? (bool) $value : (bool) $setting->value;
    }

    private function hasRecentActivity(User $user): bool
    {
        if (! class_exists(AuditLog::class)) {
            return false;
        }

        return AuditLog::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDays(3))
            ->exists();
    }

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        if (EmailSendLog::alreadySent($user->id, 'onboarding_reminder', $emailNumber)) {
            return true;
        }

        // Skip onboarding email 1 if welcome sequence email 2 was already sent (cross-sequence dedup)
        if ($emailNumber === 1) {
            return EmailSendLog::alreadySent($user->id, 'welcome_sequence', 2);
        }

        return false;
    }
}
