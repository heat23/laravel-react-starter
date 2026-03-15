<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ReEngagementNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReEngagementEmails extends Command
{
    protected $signature = 'emails:send-re-engagement';

    protected $description = 'Send re-engagement emails to inactive users (7, 14, 30 days)';

    /** @var array<int, array{days: int, maxDays: int}> */
    private const EMAIL_SCHEDULE = [
        1 => ['days' => 7, 'maxDays' => 9],
        2 => ['days' => 14, 'maxDays' => 16],
        3 => ['days' => 30, 'maxDays' => 35],
    ];

    public function handle(): int
    {
        $totalSent = 0;

        foreach (self::EMAIL_SCHEDULE as $emailNumber => $schedule) {
            $sent = $this->sendEmailNumber($emailNumber, $schedule['days'], $schedule['maxDays']);
            $totalSent += $sent;
        }

        $this->info("Sent {$totalSent} re-engagement emails.");

        return self::SUCCESS;
    }

    private function sendEmailNumber(int $emailNumber, int $minDays, int $maxDays): int
    {
        $users = User::query()
            ->whereNotNull('email_verified_at')
            ->where(function ($query) use ($minDays, $maxDays) {
                $query->where(function ($q) use ($minDays, $maxDays) {
                    $q->whereNotNull('last_active_at')
                        ->where('last_active_at', '<=', now()->subDays($minDays))
                        ->where('last_active_at', '>', now()->subDays($maxDays));
                })->orWhere(function ($q) use ($minDays, $maxDays) {
                    // Fallback to last_login_at if last_active_at is null
                    $q->whereNull('last_active_at')
                        ->whereNotNull('last_login_at')
                        ->where('last_login_at', '<=', now()->subDays($minDays))
                        ->where('last_login_at', '>', now()->subDays($maxDays));
                })->orWhere(function ($q) use ($minDays, $maxDays) {
                    // Fallback to created_at if neither activity timestamp exists
                    $q->whereNull('last_active_at')
                        ->whereNull('last_login_at')
                        ->where('created_at', '<=', now()->subDays($minDays))
                        ->where('created_at', '>', now()->subDays($maxDays));
                });
            })
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            try {
                $user->notify(new ReEngagementNotification($emailNumber));
                $sent++;

                Log::info('Re-engagement email sent', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send re-engagement email', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        return $user->notifications()
            ->where('type', ReEngagementNotification::class)
            ->where('data', 'like', '%"email_number":'.$emailNumber.'%')
            ->exists();
    }
}
