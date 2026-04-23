<?php

use App\Models\User;
use App\Notifications\RefundProcessedNotification;
use App\Webhooks\Stripe\Dto\StripeEvent;
use App\Webhooks\Stripe\Handlers\ChargeRefundedHandler;
use App\Webhooks\Stripe\Handlers\InvoicePaymentFailedHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['features.notifications.enabled' => true]);
    ensureCashierTablesExist();
    Cache::flush();
});

it('only sends a notification once when a charge.refunded event is dispatched twice with the same id', function () {
    Notification::fake();

    $user = User::factory()->create(['stripe_id' => 'cus_dedup_refund']);

    $eventId = 'evt_dedup_charge_'.uniqid();

    $payload = [
        'id' => $eventId,
        'type' => 'charge.refunded',
        'created' => now()->timestamp,
        'data' => [
            'object' => [
                'id' => 'ch_dedup_test',
                'customer' => 'cus_dedup_refund',
                'amount_refunded' => 1000,
                'currency' => 'usd',
                'refunds' => ['data' => [['reason' => null]]],
            ],
        ],
    ];

    $handler = app(ChargeRefundedHandler::class);
    $event = new StripeEvent($payload);

    // First dispatch — should process
    $handler->handle($event);
    // Second dispatch of identical event — should be skipped
    $handler->handle($event);

    // Notification must be sent exactly once, not twice
    Notification::assertSentTo($user, RefundProcessedNotification::class, 1);
});

it('only marks subscription past_due once when invoice.payment_failed is received twice', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_dedup_failed']);
    $subscription = createSubscription($user, [
        'stripe_id' => 'sub_dedup_failed',
        'past_due_since' => null,
    ]);

    $eventId = 'evt_dedup_failed_'.uniqid();

    $payload = [
        'id' => $eventId,
        'type' => 'invoice.payment_failed',
        'created' => now()->timestamp,
        'data' => [
            'object' => [
                'id' => 'in_dedup_failed',
                'customer' => 'cus_dedup_failed',
                'subscription' => 'sub_dedup_failed',
                'amount_due' => 1900,
            ],
        ],
    ];

    $handler = app(InvoicePaymentFailedHandler::class);
    $event = new StripeEvent($payload);

    // First dispatch — sets past_due_since
    $handler->handle($event);
    $subscription->refresh();
    expect($subscription->past_due_since)->not->toBeNull();

    // Reset to simulate idempotency-safe scenario: clear past_due_since again
    // then replay — second call should be a no-op (dedup guard fires)
    $subscription->past_due_since = null;
    $subscription->save();

    $handler->handle($event);
    $subscription->refresh();

    // Second dispatch was skipped — past_due_since should still be null
    expect($subscription->past_due_since)->toBeNull();
});

it('different event ids are both processed independently', function () {
    Notification::fake();

    $user = User::factory()->create(['stripe_id' => 'cus_dedup_distinct']);

    $makePayload = fn (string $id) => [
        'id' => $id,
        'type' => 'charge.refunded',
        'created' => now()->timestamp,
        'data' => [
            'object' => [
                'id' => 'ch_distinct_'.$id,
                'customer' => 'cus_dedup_distinct',
                'amount_refunded' => 500,
                'currency' => 'usd',
                'refunds' => ['data' => [['reason' => null]]],
            ],
        ],
    ];

    $handler = app(ChargeRefundedHandler::class);

    $handler->handle(new StripeEvent($makePayload('evt_distinct_aaa')));
    $handler->handle(new StripeEvent($makePayload('evt_distinct_bbb')));

    // Two distinct events → two notifications
    Notification::assertSentTo($user, RefundProcessedNotification::class, 2);
});
