<?php

namespace App\Console\Commands;

use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class PruneStaleWebhookDeliveries extends Command
{
    protected $signature = 'webhooks:prune-stale {--hours=1 : Mark deliveries stale after this many hours}';

    protected $description = 'Mark orphaned webhook deliveries as abandoned';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $stale = WebhookDelivery::where('status', 'pending')
            ->where('created_at', '<', now()->subHours($hours))
            ->update(['status' => 'abandoned']);

        $this->info("Marked {$stale} stale webhook deliveries as abandoned.");

        return self::SUCCESS;
    }
}
