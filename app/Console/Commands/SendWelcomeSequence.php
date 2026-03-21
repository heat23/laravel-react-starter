<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\WelcomeSequenceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWelcomeSequence extends Command
{
    protected $signature = 'emails:send-welcome-sequence';

    protected $description = 'Send day-1 and day-3 follow-up emails in the welcome sequence (email 1 is sent by the Registered event listener)';

    /** @var array<int, array{days: int, maxDays: int}> */
    private const EMAIL_SCHEDULE = [
        2 => ['days' => 1, 'maxDays' => 2],
        3 => ['days' => 3, 'maxDays' => 5],
    ];

    public function handle(): int
    {
        $totalSent = 0;

        foreach (self::EMAIL_SCHEDULE as $emailNumber => $schedule) {
            $sent = $this->sendEmailNumber($emailNumber, $schedule['days'], $schedule['maxDays']);
            $totalSent += $sent;
        }

        $this->info("Sent {$totalSent} welcome sequence emails.");

        return self::SUCCESS;
    }

    private function sendEmailNumber(int $emailNumber, int $minDays, int $maxDays): int
    {
        $query = User::query()
            ->whereNotNull('email_verified_at');

        if ($minDays > 0) {
            $query->where('created_at', '<=', now()->subDays($minDays));
        }
        $query->where('created_at', '>', now()->subDays($maxDays));

        $users = $query->get();
        $sent = 0;

        foreach ($users as $user) {
            if ($this->hasOptedOut($user)) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            try {
                $user->notify(new WelcomeSequenceNotification($emailNumber));
                $sent++;

                Log::info('Welcome sequence email sent', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send welcome sequence email', [
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

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        return $user->notifications()
            ->where('type', WelcomeSequenceNotification::class)
            ->where('data->email_number', $emailNumber)
            ->exists();
    }
}
