<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneReadNotifications extends Command
{
    protected $signature = 'prune-read-notifications {--days=30 : Number of days to retain read notifications}';

    protected $description = 'Delete read notifications older than the retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} read notifications older than {$days} days.");

        return self::SUCCESS;
    }
}
