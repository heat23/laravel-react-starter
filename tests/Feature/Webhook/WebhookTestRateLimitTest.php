<?php

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    config(['features.webhooks.enabled' => true]);
    Queue::fake();
    // Clear all rate limiters for the test IP
    RateLimiter::clear('127.0.0.1');
    RateLimiter::clear('webhook-test:127.0.0.1');

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

it('rate limits webhook test dispatch to 5 per minute', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create();

    // Make 5 requests (the limit)
    for ($i = 0; $i < 5; $i++) {
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/webhooks/{$endpoint->id}/test");

        $response->assertOk();
    }

    // 6th request should be rate limited
    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/webhooks/{$endpoint->id}/test");

    $response->assertStatus(429);
});

it('allows webhook test dispatch within rate limit', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/webhooks/{$endpoint->id}/test");

    $response->assertOk();
    $response->assertJson(['success' => true, 'message' => 'Test webhook queued.']);
});
