<?php

namespace App\Jobs;

use App\Services\AnalyticsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        private readonly string $eventName,
        private readonly array $params,
        private readonly int $userId,
    ) {}

    public function handle(AnalyticsGateway $gateway): void
    {
        $gateway->send($this->eventName, $this->params, $this->userId);
    }
}
