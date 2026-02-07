<?php

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['features.webhooks.enabled' => true]);
    Queue::fake();

    // Manually create webhook tables for testing since feature-gated migration skips them
    if (! \Schema::hasTable('webhook_endpoints')) {
        \Schema::create('webhook_endpoints', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->json('events');
            $table->text('secret');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'active']);
        });
    }
    if (! \Schema::hasTable('webhook_deliveries')) {
        \Schema::create('webhook_deliveries', function ($table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('event_type');
            $table->json('payload');
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['webhook_endpoint_id', 'created_at']);
            $table->index('status');
        });
    }
});

it('returns 404 when webhooks feature is disabled', function () {
    config(['features.webhooks.enabled' => false]);
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/webhooks');

    $response->assertNotFound();
});

it('lists webhook endpoints for authenticated user', function () {
    $user = User::factory()->create();
    WebhookEndpoint::factory()->for($user)->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/webhooks');

    $response->assertOk();
    expect($response->json())->toHaveCount(3);
});

it('does not list other users endpoints', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    WebhookEndpoint::factory()->for($other)->count(2)->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/webhooks');

    $response->assertOk();
    expect($response->json())->toHaveCount(0);
});

it('creates a webhook endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/webhooks', [
        'url' => 'https://example.com/webhook',
        'events' => ['user.created'],
        'description' => 'Test endpoint',
    ]);

    $response->assertCreated();
    expect($response->json())->toHaveKeys(['id', 'secret']);
    expect($user->webhookEndpoints()->count())->toBe(1);
});

it('validates url is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/webhooks', [
        'events' => ['user.created'],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('url');
});

it('validates events are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/webhooks', [
        'url' => 'https://example.com/webhook',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('events');
});

it('validates events must be from allowed list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/webhooks', [
        'url' => 'https://example.com/webhook',
        'events' => ['invalid.event'],
    ]);

    $response->assertUnprocessable();
});

it('enforces plan limits on endpoint creation', function () {
    config(['features.webhooks.max_endpoints_free' => 2]);
    $user = User::factory()->create();
    WebhookEndpoint::factory()->for($user)->count(2)->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/webhooks', [
        'url' => 'https://example.com/webhook',
        'events' => ['user.created'],
    ]);

    $response->assertUnprocessable();
    expect($response->json('message'))->toContain('maximum');
});

it('updates a webhook endpoint', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create();

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/webhooks/{$endpoint->id}", [
        'description' => 'Updated description',
        'active' => false,
    ]);

    $response->assertOk();
    expect($endpoint->fresh()->description)->toBe('Updated description');
    expect($endpoint->fresh()->active)->toBeFalse();
});

it('deletes a webhook endpoint', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create();

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/webhooks/{$endpoint->id}");

    $response->assertOk();
    expect($user->webhookEndpoints()->count())->toBe(0);
});

it('cannot update another users endpoint', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($other)->create();

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/webhooks/{$endpoint->id}", [
        'description' => 'Hacked',
    ]);

    $response->assertNotFound();
});

it('returns deliveries for an endpoint', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create();
    \App\Models\WebhookDelivery::factory()->for($endpoint, 'endpoint')->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/webhooks/{$endpoint->id}/deliveries");

    $response->assertOk();
    expect($response->json())->toHaveCount(3);
});

it('requires authentication', function () {
    $response = $this->getJson('/api/webhooks');

    $response->assertUnauthorized();
});
