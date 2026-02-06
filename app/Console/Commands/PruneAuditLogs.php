<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune {--days= : Number of days to retain}';

    protected $description = 'Delete audit log records older than the retention period';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('health.audit_retention_days', 90));
        $cutoff = now()->subDays($days);
        $totalDeleted = 0;

        do {
            $deleted = AuditLog::where('created_at', '<', $cutoff)->limit(1000)->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        $this->info("Pruned {$totalDeleted} audit log records older than {$days} days.");

        return self::SUCCESS;
    }
}
