<?php

namespace App\Console\Commands;

use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class PruneOldWebhookDeliveries extends Command
{
    protected $signature = 'webhooks:prune-old {--days=90 : Delete terminal deliveries older than this many days}';

    protected $description = 'Delete old webhook deliveries in terminal state (delivered, failed, abandoned)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);
        $totalDeleted = 0;

        do {
            $deleted = WebhookDelivery::whereIn('status', ['success', 'failed', 'abandoned'])
                ->where('created_at', '<', $cutoff)
                ->limit(1000)
                ->delete();

            $totalDeleted += $deleted;
        } while ($deleted > 0);

        $this->info("Pruned {$totalDeleted} old webhook deliveries older than {$days} days.");

        return self::SUCCESS;
    }
}
