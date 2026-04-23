<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class SubscriptionTrialWillEndHandler implements StripeEventHandler
{
    use DeduplicatesStripeEvents;

    public function handle(StripeEvent $event): void
    {
        if ($this->alreadyProcessed($event->payload['id'] ?? '')) {
            return;
        }

        Log::channel('single')->info('Stripe webhook: subscription.trial_will_end', [
            'event_id' => $event->payload['id'] ?? null,
            'stripe_customer' => $event->payload['data']['object']['customer'] ?? null,
        ]);
    }
}
