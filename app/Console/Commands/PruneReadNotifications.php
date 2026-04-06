<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneReadNotifications extends Command
{
    /**
     * Minimum safe retention in days.
     *
     * Lifecycle sequences (win-back, re-engagement) look back up to 33 days
     * post-event for their final email send window. Setting retention below
     * this floor risks pruning in-app notification history before the full
     * sequence completes. Email dedup itself is handled by EmailSendLog (not
     * the notifications table), so this floor protects user-visible history only.
     */
    protected const MIN_SAFE_DAYS = 60;

    protected $signature = 'prune-read-notifications {--days=60 : Number of days to retain read notifications}';

    protected $description = 'Delete read notifications older than the retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < self::MIN_SAFE_DAYS) {
            Log::warning('prune-read-notifications: retention period below recommended minimum', [
                'days' => $days,
                'min_safe_days' => self::MIN_SAFE_DAYS,
                'reason' => 'Lifecycle sequences run up to 33 days post-event; 60-day floor preserves in-app notification history for active sequences.',
            ]);
        }

        $cutoff = now()->subDays($days);

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} read notifications older than {$days} days.");

        return self::SUCCESS;
    }
}
