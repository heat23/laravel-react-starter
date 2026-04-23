<?php

use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\StripeEventMap;

it('has a handler mapped for every event exercised by the Stripe webhook test suite', function () {
    $exercisedEvents = [
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'customer.subscription.trial_will_end',
        'invoice.payment_succeeded',
        'invoice.payment_failed',
        'invoice.payment_action_required',
        'customer.updated',
        'charge.refunded',
    ];

    foreach ($exercisedEvents as $eventType) {
        $handlerClass = StripeEventMap::get($eventType);
        expect($handlerClass)->not->toBeNull("No handler mapped for '{$eventType}'");
        expect(class_exists($handlerClass))->toBeTrue("Handler class '{$handlerClass}' does not exist");
        expect(is_a($handlerClass, StripeEventHandler::class, true))->toBeTrue("'{$handlerClass}' must implement StripeEventHandler");
    }
});

it('returns null for unknown event types', function () {
    expect(StripeEventMap::get('unknown.event.type'))->toBeNull();
    expect(StripeEventMap::get(''))->toBeNull();
});

it('all() returns all mapped handlers implementing StripeEventHandler', function () {
    $all = StripeEventMap::all();

    expect($all)->not->toBeEmpty();

    foreach ($all as $eventType => $handlerClass) {
        expect($handlerClass)->toBeString();
        expect(class_exists($handlerClass))->toBeTrue();
        expect(is_a($handlerClass, StripeEventHandler::class, true))->toBeTrue();
    }
});

it('has at least 9 handlers in the map', function () {
    expect(count(StripeEventMap::all()))->toBeGreaterThanOrEqual(9);
});
