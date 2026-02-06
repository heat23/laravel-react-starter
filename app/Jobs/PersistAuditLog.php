<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PersistAuditLog implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly string $event,
        private readonly ?int $userId,
        private readonly ?string $ip,
        private readonly ?string $userAgent,
        private readonly ?array $metadata,
    ) {}

    public function handle(): void
    {
        try {
            AuditLog::create([
                'event' => $this->event,
                'user_id' => $this->userId,
                'ip' => $this->ip,
                'user_agent' => $this->userAgent,
                'metadata' => $this->metadata ?: null,
            ]);
        } catch (\Throwable $e) {
            Log::channel('single')->error('Failed to persist audit log to DB', [
                'event' => $this->event,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
