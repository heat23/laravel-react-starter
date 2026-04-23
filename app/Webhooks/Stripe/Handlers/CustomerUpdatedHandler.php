<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class CustomerUpdatedHandler implements StripeEventHandler
{
    public function handle(StripeEvent $event): void
    {
        Log::channel('single')->info('Stripe webhook: customer.updated', [
            'event_id' => $event->payload['id'] ?? null,
            'stripe_customer' => $event->payload['data']['object']['id'] ?? null,
        ]);
    }
}
