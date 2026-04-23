<?php

use App\Models\IncomingWebhook;
use App\Webhooks\Providers\CustomWebhookProvider;
use App\Webhooks\Providers\GithubWebhookProvider;

beforeEach(function () {
    config([
        'features.webhooks.enabled' => true,
        'webhooks.incoming.providers.github' => [
            'class' => GithubWebhookProvider::class,
            'secret' => 'test-github-secret',
            'signature_header' => 'X-Hub-Signature-256',
            'algorithm' => 'sha256',
        ],
        'webhooks.incoming.providers.custom' => [
            'class' => CustomWebhookProvider::class,
            'secret' => 'test-custom-secret',
            'signature_header' => 'X-Webhook-Signature',
            'algorithm' => 'sha256',
        ],
    ]);

    if (! Schema::hasTable('incoming_webhooks')) {
        Schema::create('incoming_webhooks', function ($table) {
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

it('accepts valid github webhook and stores it', function () {
    $body = json_encode(['action' => 'push', 'ref' => 'refs/heads/main']);
    $signature = 'sha256='.hash_hmac('sha256', $body, 'test-github-secret');

    $response = $this->postJson('/api/webhooks/incoming/github', json_decode($body, true), [
        'X-Hub-Signature-256' => $signature,
        'X-GitHub-Delivery' => 'delivery-github-1',
        'X-GitHub-Event' => 'push',
    ]);

    $response->assertOk();
    expect(IncomingWebhook::where('provider', 'github')->count())->toBe(1);
    expect(IncomingWebhook::first()->external_id)->toBe('delivery-github-1');
});

it('rejects github webhook with bad signature', function () {
    $response = $this->postJson('/api/webhooks/incoming/github', ['action' => 'push'], [
        'X-Hub-Signature-256' => 'sha256=invalidsignature',
        'X-GitHub-Delivery' => 'delivery-github-2',
        'X-GitHub-Event' => 'push',
    ]);

    $response->assertForbidden();
    expect(IncomingWebhook::count())->toBe(0);
});

it('accepts valid custom webhook and stores it', function () {
    $body = json_encode(['id' => 'evt_custom_1', 'type' => 'data.synced']);
    $signature = hash_hmac('sha256', $body, 'test-custom-secret');

    $response = $this->postJson('/api/webhooks/incoming/custom', json_decode($body, true), [
        'X-Webhook-Signature' => $signature,
    ]);

    $response->assertOk();
    expect(IncomingWebhook::where('provider', 'custom')->count())->toBe(1);
});

it('rejects custom webhook with bad signature', function () {
    $response = $this->postJson('/api/webhooks/incoming/custom', ['id' => 'evt_custom_2', 'type' => 'data.synced'], [
        'X-Webhook-Signature' => 'invalidsignature',
    ]);

    $response->assertForbidden();
});

it('rejects unknown provider with 403', function () {
    $response = $this->postJson('/api/webhooks/incoming/unknownprovider', ['data' => 'test']);

    $response->assertForbidden();
});
