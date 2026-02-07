<?php

use App\Models\IncomingWebhook;

beforeEach(function () {
    config(['features.webhooks.enabled' => true]);
    config(['webhooks.incoming.providers.github.secret' => 'test-secret-key']);
    config(['webhooks.incoming.replay_tolerance' => 300]);

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

it('accepts valid github webhook', function () {
    $payload = json_encode(['action' => 'push', 'ref' => 'refs/heads/main']);
    $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret-key');

    $response = $this->postJson('/api/webhooks/incoming/github', json_decode($payload, true), [
        'X-Hub-Signature-256' => $signature,
        'X-GitHub-Delivery' => 'delivery-123',
        'X-GitHub-Event' => 'push',
    ]);

    $response->assertOk();
    expect(IncomingWebhook::count())->toBe(1);
    expect(IncomingWebhook::first()->provider)->toBe('github');
});

it('rejects webhook with invalid signature', function () {
    $payload = json_encode(['action' => 'push']);

    $response = $this->postJson('/api/webhooks/incoming/github', json_decode($payload, true), [
        'X-Hub-Signature-256' => 'sha256=invalid-signature',
        'X-GitHub-Delivery' => 'delivery-456',
    ]);

    $response->assertForbidden();
});

it('rejects webhook without signature', function () {
    $response = $this->postJson('/api/webhooks/incoming/github', ['action' => 'push']);

    $response->assertForbidden();
});

it('handles idempotent webhook delivery', function () {
    $payload = json_encode(['action' => 'push']);
    $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret-key');

    // First delivery
    $this->postJson('/api/webhooks/incoming/github', json_decode($payload, true), [
        'X-Hub-Signature-256' => $signature,
        'X-GitHub-Delivery' => 'delivery-789',
        'X-GitHub-Event' => 'push',
    ])->assertOk();

    // Duplicate delivery - same external_id
    $this->postJson('/api/webhooks/incoming/github', json_decode($payload, true), [
        'X-Hub-Signature-256' => $signature,
        'X-GitHub-Delivery' => 'delivery-789',
        'X-GitHub-Event' => 'push',
    ])->assertOk();

    expect(IncomingWebhook::count())->toBe(1);
});
