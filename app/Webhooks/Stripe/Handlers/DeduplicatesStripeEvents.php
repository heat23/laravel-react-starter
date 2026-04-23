<?php

namespace App\Webhooks\Stripe\Handlers;

use Illuminate\Support\Facades\Cache;

/**
 * Guards Stripe event handlers against duplicate deliveries.
 *
 * Stripe guarantees at-least-once delivery, meaning the same event ID can
 * arrive more than once. This trait uses a short-lived cache entry keyed by
 * event ID to ensure the handler body runs at most once per event.
 */
trait DeduplicatesStripeEvents
{
    /**
     * Returns true when this event has already been processed by this handler.
     * On first call the cache entry is created (Cache::add is atomic) and false
     * is returned; subsequent calls within the TTL return true.
     */
    protected function alreadyProcessed(string $eventId): bool
    {
        return ! Cache::add("stripe_event:{$eventId}", true, now()->addHour());
    }
}
