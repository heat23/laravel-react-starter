<?php

use App\Jobs\DispatchWebhookJob;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\AuditService;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    config(['features.webhooks.enabled' => true]);
    Queue::fake();

    // Create webhook tables for in-memory SQLite test runs when migrations
    // are feature-gated and therefore not present.
    if (! Schema::hasTable('webhook_endpoints')) {
        Schema::create('webhook_endpoints', function ($table) {
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

    if (! Schema::hasTable('webhook_deliveries')) {
        Schema::create('webhook_deliveries', function ($table) {
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

it('rejects creating a webhook endpoint targeting a loopback address', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/webhooks', [
        'url' => 'http://127.0.0.1/steal-secrets',
        'events' => ['user.created'],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('url');
});

it('rejects creating a webhook endpoint targeting an RFC-1918 address', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/webhooks', [
        'url' => 'http://192.168.1.1/internal-api',
        'events' => ['user.created'],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('url');
});

it('rejects creating a webhook endpoint targeting the AWS IMDS endpoint', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/webhooks', [
        'url' => 'http://169.254.169.254/latest/meta-data/',
        'events' => ['user.created'],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('url');
});

it('rejects updating a webhook endpoint to an internal address', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create([
        'url' => 'https://example.com/webhook',
    ]);
    Sanctum::actingAs($user, ['*']);

    $response = $this->patchJson("/api/webhooks/{$endpoint->id}", [
        'url' => 'http://10.0.0.1/internal',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('url');
});

it('blocks dispatch job when a webhook endpoint url resolves to a private ip', function () {
    // Create an endpoint with an internal IP directly (bypassing form validation to
    // simulate a URL that was valid at create-time but now resolves internally,
    // or was inserted via a different path).
    $endpoint = WebhookEndpoint::factory()->create([
        'url' => 'http://127.0.0.1/hook',
        'active' => true,
    ]);

    $delivery = WebhookDelivery::create([
        'webhook_endpoint_id' => $endpoint->id,
        'uuid' => Str::uuid()->toString(),
        'event_type' => 'user.created',
        'payload' => ['id' => 1],
        'status' => 'pending',
    ]);

    // Run the job synchronously (Queue::fake does not prevent direct handle() calls).
    app(DispatchWebhookJob::class, ['deliveryId' => $delivery->id])->handle(
        app(WebhookService::class),
        app(AuditService::class),
    );

    $delivery->refresh();

    expect($delivery->status)->toBe('failed');
    expect($delivery->response_body)->toStartWith('BLOCKED:');
});
