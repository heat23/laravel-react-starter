<?php

namespace App\Listeners;

use App\Events\PqlThresholdReached;
use App\Notifications\LimitThresholdNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendPqlUpgradeNudge implements ShouldQueue
{
    public function handle(PqlThresholdReached $event): void
    {
        // Only fire for 80% and 100% thresholds
        if ($event->threshold < 80) {
            return;
        }

        // Suppress if already notified within 14 days for this limit+threshold combo
        $suppressKey = "pql_nudge:{$event->user->id}:{$event->limitKey}:{$event->threshold}";
        if (Cache::has($suppressKey)) {
            return;
        }

        try {
            $event->user->notify(new LimitThresholdNotification($event->limitKey, $event->threshold));
            Cache::put($suppressKey, true, now()->addDays(14));
        } catch (\Throwable $e) {
            Log::warning('pql_upgrade_nudge_failed', [
                'user_id' => $event->user->id,
                'limit_key' => $event->limitKey,
                'threshold' => $event->threshold,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
