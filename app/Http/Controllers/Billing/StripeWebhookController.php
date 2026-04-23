<?php

namespace App\Http\Controllers\Billing;

use App\Models\Subscription;
use App\Webhooks\Stripe\Dto\StripeEvent;
use App\Webhooks\Stripe\StripeEventMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    public function __construct()
    {
        if (! app()->environment('local') && empty(config('cashier.webhook.secret'))) {
            throw new \RuntimeException(
                'STRIPE_WEBHOOK_SECRET must be set in non-local environments. '
                .'Without it, webhook signature verification is disabled, allowing forged events.'
            );
        }
        parent::__construct();
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        $type = $payload['type'] ?? '';

        if ($type === 'customer.subscription.updated') {
            return $this->handleCustomerSubscriptionUpdated($payload);
        }

        if (in_array($type, ['customer.subscription.created', 'customer.subscription.deleted'], true)) {
            $response = parent::handleWebhook($request);
            app(StripeEventMap::get($type))->handle(new StripeEvent($payload));

            return $response;
        }

        if ($handlerClass = StripeEventMap::get($type)) {
            app($handlerClass)->handle(new StripeEvent($payload));
        } else {
            Log::warning("Unhandled Stripe webhook event: {$type}");
        }

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $subId = $payload['data']['object']['id'] ?? null;
        $ts = $payload['created'] ?? null;
        $sub = $subId ? Subscription::where('stripe_id', $subId)->first() : null;

        // Equal timestamps are legitimate cascading events (e.g. Stripe fires
        // multiple events in the same second for a single user action). Only
        // strict "less-than" is truly out-of-order; equal means "process again".
        if ($sub && $sub->last_webhook_at && $ts && $ts < $sub->last_webhook_at) {
            Log::warning('Out-of-order webhook rejected', ['subscription_id' => $subId, 'event_timestamp' => $ts, 'last_processed_at' => $sub->last_webhook_at]);

            return $this->successMethod();
        }

        $response = parent::handleCustomerSubscriptionUpdated($payload);
        $subId && $ts && Subscription::where('stripe_id', $subId)->update(['last_webhook_at' => $ts]);
        app(StripeEventMap::get('customer.subscription.updated'))->handle(new StripeEvent($payload));

        return $response;
    }
}
