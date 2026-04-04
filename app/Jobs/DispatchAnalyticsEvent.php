<?php

namespace App\Jobs;

use App\Services\AnalyticsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Async GA4 Measurement Protocol dispatch.
 * External HTTP calls must live in jobs (per CLAUDE.md) to keep them
 * out of the request lifecycle and allow queue retries.
 */
class DispatchAnalyticsEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    /** Exponential backoff: 10s then 60s between retry attempts. */
    public array $backoff = [10, 60];

    public function __construct(
        public readonly string $eventName,
        public readonly array $params,
        public readonly int $userId,
    ) {}

    public function handle(AnalyticsGateway $gateway): void
    {
        $gateway->send($this->eventName, $this->params, $this->userId);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('DispatchAnalyticsEvent failed after all retries', [
            'event_name' => $this->eventName,
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);

        // Route through Laravel's exception handler for proactive alerting
        // (Sentry, Flare, or any configured error reporting channel).
        report($e);
    }
}
