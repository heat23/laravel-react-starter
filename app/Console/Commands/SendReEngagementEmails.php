<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\ReEngagementNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendReEngagementEmails extends Command
{
    protected $signature = 'emails:send-re-engagement';

    protected $description = 'Send re-engagement emails to inactive users (7, 14, 21, 30 days)';

    /** @var array<int, array{days: int, maxDays: int}> */
    private const EMAIL_SCHEDULE = [
        1 => ['days' => 7, 'maxDays' => 9],
        2 => ['days' => 14, 'maxDays' => 16],
        3 => ['days' => 30, 'maxDays' => 35],
        4 => ['days' => 21, 'maxDays' => 23],
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
            if ($this->hasOptedOut($user)) {
                continue;
            }

            if ($this->alreadySentEmail($user, $emailNumber)) {
                continue;
            }

            // Skip emails 2+ if the user re-engaged after the prior email was sent
            if ($emailNumber > 1 && $this->hasReengaged($user, $emailNumber)) {
                continue;
            }

            $isPaidUser = $this->hasPaidSubscription($user);

            try {
                $user->notify(new ReEngagementNotification($emailNumber, $isPaidUser));
                $sent++;

                Log::info('Re-engagement email sent', [
                    'user_id' => $user->id,
                    'email_number' => $emailNumber,
                    'is_paid' => $isPaidUser,
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

    /**
     * Check if the user re-engaged after the prior sequence email.
     * If last_active_at > prior email sent_at, the re-engagement was successful — skip.
     */
    private function hasReengaged(User $user, int $emailNumber): bool
    {
        $priorEmailNumber = $emailNumber - 1;
        $priorEmailSentAt = $user->notifications()
            ->where('type', ReEngagementNotification::class)
            ->where('data', 'like', '%"email_number":'.$priorEmailNumber.'%')
            ->value('created_at');

        if (! $priorEmailSentAt) {
            return false;
        }

        $lastActive = $user->last_active_at ?? $user->last_login_at;

        return $lastActive !== null && $lastActive > $priorEmailSentAt;
    }

    private function hasPaidSubscription(User $user): bool
    {
        if (! config('features.billing.enabled', false)) {
            return false;
        }

        return DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->exists();
    }

    private function hasOptedOut(User $user): bool
    {
        $value = UserSetting::getValue($user->id, 'marketing_emails', true);

        return $value === false || $value === '0' || $value === 0;
    }

    private function alreadySentEmail(User $user, int $emailNumber): bool
    {
        return $user->notifications()
            ->where('type', ReEngagementNotification::class)
            ->where('data', 'like', '%"email_number":'.$emailNumber.'%')
            ->exists();
    }
}
