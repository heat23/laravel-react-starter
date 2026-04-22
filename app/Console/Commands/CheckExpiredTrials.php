<?php

namespace App\Console\Commands;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Console\Command;

class CheckExpiredTrials extends Command
{
    protected $signature = 'trials:check-expired';

    protected $description = 'Log TRIAL_EXPIRED events for users whose trial has ended without subscribing';

    public function handle(AuditService $auditService): int
    {
        if (! config('plans.trial.enabled', false)) {
            $this->info('Trials are disabled — skipping.');

            return self::SUCCESS;
        }

        // Lifetime idempotency: exclude users who have EVER had a TRIAL_EXPIRED event logged.
        // This is safer than a sliding window (which breaks if scheduler is down for 2+ days).
        $alreadyExpiredIds = AuditLog::query()
            ->where('event', AuditEvent::TRIAL_EXPIRED->value)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        User::query()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->whereDoesntHave('subscriptions', fn ($q) => $q->where('stripe_status', 'active'))
            ->whereNotIn('id', $alreadyExpiredIds)
            ->chunk(100, function ($users) use ($auditService) {
                foreach ($users as $user) {
                    $auditService->log(AuditEvent::TRIAL_EXPIRED, [
                        'user_id' => $user->id,
                        'trial_ends_at' => $user->trial_ends_at?->toISOString(),
                    ]);
                }
            });

        $this->info('trials:check-expired complete.');

        return self::SUCCESS;
    }
}
