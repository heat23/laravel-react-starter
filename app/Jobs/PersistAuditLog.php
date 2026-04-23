<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PersistAuditLog implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * Deduplicate queue entries for 1 hour using the idempotency key.
     * Prevents double-processing when the same job is dispatched twice or
     * a failed job is retried after a partial side-effect.
     */
    public int $uniqueFor = 3600;

    public function __construct(
        private readonly string $event,
        private readonly ?int $userId,
        private readonly ?string $ip,
        private readonly ?string $userAgent,
        private readonly ?array $metadata,
        private readonly ?string $idempotencyKey = null,
    ) {}

    /**
     * Unique queue identifier — uses the idempotency key when available,
     * falling back to a no-op key so jobs without a key are never deduplicated.
     */
    public function uniqueId(): string
    {
        return $this->idempotencyKey ?? '';
    }

    public function handle(): void
    {
        try {
            AuditLog::create([
                'event' => $this->event,
                'user_id' => $this->userId,
                'ip' => $this->ip,
                'user_agent' => $this->userAgent,
                'metadata' => $this->metadata ?: null,
                'idempotency_key' => $this->idempotencyKey,
            ]);
        } catch (QueryException $e) {
            // Unique constraint violation on idempotency_key — a duplicate delivery
            // arrived after the job already ran. Swallow and log at INFO level.
            // MySQL error code 1062; SQLite raises "UNIQUE constraint failed".
            $isDuplicate = ($e->errorInfo[1] ?? 0) === 1062
                || str_contains($e->getMessage(), 'UNIQUE constraint failed')
                || str_contains($e->getMessage(), 'idempotency_key');

            if ($isDuplicate) {
                Log::info('audit_log_duplicate_skipped', [
                    'event' => $this->event,
                    'idempotency_key' => $this->idempotencyKey,
                ]);

                return;
            }

            Log::channel('single')->error('Failed to persist audit log to DB', [
                'event' => $this->event,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::channel('single')->error('Failed to persist audit log to DB', [
                'event' => $this->event,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
