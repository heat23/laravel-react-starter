<?php

namespace App\Listeners;

use App\Events\LeadQualifiedEvent;
use App\Notifications\UpgradeNudgeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadQualifiedListener implements ShouldQueue
{
    public function handle(LeadQualifiedEvent $event): void
    {
        if ($event->stage === 'mql') {
            // Respect marketing opt-out preference.
            // Intentionally returns before writing the suppression cache key so that if a user
            // opts back in they receive a fresh 30-day notification window rather than inheriting
            // a window that started while they were opted out.
            if ($event->user->marketing_opt_out) {
                return;
            }

            // Suppress if already notified within 30 days
            $suppressKey = "lead_qualified_nudge:{$event->user->id}:mql";
            if (Cache::has($suppressKey)) {
                return;
            }

            try {
                $event->user->notify(new UpgradeNudgeNotification($event->score));
                Cache::put($suppressKey, true, now()->addDays(30));
            } catch (\Throwable $e) {
                Log::warning('lead_qualified_nudge_failed', [
                    'user_id' => $event->user->id,
                    'score' => $event->score,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($event->stage === 'sql') {
            // Set sql_qualified_at only once (atomic to prevent concurrent writes)
            DB::table('users')
                ->where('id', $event->user->id)
                ->whereNull('sql_qualified_at')
                ->update(['sql_qualified_at' => now()]);

            Log::info('lead_sql_qualified', [
                'user_id' => $event->user->id,
                'score' => $event->score,
            ]);
        }
    }
}
