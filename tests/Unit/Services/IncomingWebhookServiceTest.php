<?php

use App\Models\IncomingWebhook;
use App\Services\IncomingWebhookService;

beforeEach(function () {
    // Create incoming webhooks table for testing
    if (! \Schema::hasTable('incoming_webhooks')) {
        \Schema::create('incoming_webhooks', function ($table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('external_id')->nullable();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('received');
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index(['provider', 'status']);
        });
    }
});

it('processes a new incoming webhook', function () {
    $service = new IncomingWebhookService;

    $result = $service->process('github', 'ext-123', 'push', ['data' => 'test']);

    expect($result)->toBeInstanceOf(IncomingWebhook::class);
    expect($result->provider)->toBe('github');
    expect($result->external_id)->toBe('ext-123');
    expect($result->status)->toBe('received');
});

it('returns null for duplicate webhook (idempotent)', function () {
    $service = new IncomingWebhookService;

    $service->process('github', 'ext-123', 'push', ['data' => 'test']);
    $result = $service->process('github', 'ext-123', 'push', ['data' => 'test']);

    expect($result)->toBeNull();
    expect(IncomingWebhook::count())->toBe(1);
});

it('allows same external_id from different providers', function () {
    $service = new IncomingWebhookService;

    $service->process('github', 'ext-123', 'push', ['data' => 'test']);
    $result = $service->process('stripe', 'ext-123', 'charge.completed', ['data' => 'test']);

    expect($result)->not->toBeNull();
    expect(IncomingWebhook::count())->toBe(2);
});

it('checks if webhook was already processed', function () {
    $service = new IncomingWebhookService;

    expect($service->isProcessed('github', 'ext-123'))->toBeFalse();

    $service->process('github', 'ext-123', 'push', ['data' => 'test']);

    expect($service->isProcessed('github', 'ext-123'))->toBeTrue();
});

it('processes webhook without external_id (no idempotency)', function () {
    $service = new IncomingWebhookService;

    $result1 = $service->process('custom', null, 'event', ['data' => '1']);
    $result2 = $service->process('custom', null, 'event', ['data' => '2']);

    expect($result1)->not->toBeNull();
    expect($result2)->not->toBeNull();
    expect(IncomingWebhook::count())->toBe(2);
});
