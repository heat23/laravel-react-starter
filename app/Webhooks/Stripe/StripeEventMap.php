<?php

namespace App\Webhooks\Stripe;

use App\Webhooks\Stripe\Handlers\ChargeRefundedHandler;
use App\Webhooks\Stripe\Handlers\CustomerUpdatedHandler;
use App\Webhooks\Stripe\Handlers\InvoicePaymentActionRequiredHandler;
use App\Webhooks\Stripe\Handlers\InvoicePaymentFailedHandler;
use App\Webhooks\Stripe\Handlers\InvoicePaymentSucceededHandler;
use App\Webhooks\Stripe\Handlers\SubscriptionCreatedHandler;
use App\Webhooks\Stripe\Handlers\SubscriptionDeletedHandler;
use App\Webhooks\Stripe\Handlers\SubscriptionTrialWillEndHandler;
use App\Webhooks\Stripe\Handlers\SubscriptionUpdatedHandler;

class StripeEventMap
{
    /** @var array<string, class-string> */
    private static array $map = [
        'customer.subscription.created' => SubscriptionCreatedHandler::class,
        'customer.subscription.updated' => SubscriptionUpdatedHandler::class,
        'customer.subscription.deleted' => SubscriptionDeletedHandler::class,
        'customer.subscription.trial_will_end' => SubscriptionTrialWillEndHandler::class,
        'invoice.payment_succeeded' => InvoicePaymentSucceededHandler::class,
        'invoice.payment_failed' => InvoicePaymentFailedHandler::class,
        'invoice.payment_action_required' => InvoicePaymentActionRequiredHandler::class,
        'customer.updated' => CustomerUpdatedHandler::class,
        'charge.refunded' => ChargeRefundedHandler::class,
    ];

    /** @return class-string|null */
    public static function get(string $eventType): ?string
    {
        return self::$map[$eventType] ?? null;
    }

    /** @return array<string, class-string> */
    public static function all(): array
    {
        return self::$map;
    }
}
