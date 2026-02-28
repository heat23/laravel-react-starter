<?php

use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;

beforeEach(function () {
    $user = User::factory()->create();
    $this->endpoint = WebhookEndpoint::create([
        'user_id' => $user->id,
        'url' => 'https://example.com/webhook',
        'secret' => 'test-secret',
        'events' => ['user.created'],
        'is_active' => true,
    ]);
});

it('marks stale pending deliveries as abandoned', function () {
    // Create a stale delivery (2 hours old, still pending)
    $stale = WebhookDelivery::create([
        'webhook_endpoint_id' => $this->endpoint->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'event_type' => 'user.created',
        'payload' => ['test' => true],
        'status' => 'pending',
        'attempts' => 0,
    ]);
    // Backdate created_at (not in $fillable, so must use query builder)
    WebhookDelivery::where('id', $stale->id)->update(['created_at' => now()->subHours(2)]);

    // Create a recent pending delivery (should not be affected)
    $recent = WebhookDelivery::create([
        'webhook_endpoint_id' => $this->endpoint->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'event_type' => 'user.created',
        'payload' => ['test' => true],
        'status' => 'pending',
        'attempts' => 0,
    ]);

    $this->artisan('webhooks:prune-stale')->assertExitCode(0);

    expect($stale->fresh()->status)->toBe('abandoned');
    expect($recent->fresh()->status)->toBe('pending');
});

it('does not affect successful or failed deliveries', function () {
    $success = WebhookDelivery::create([
        'webhook_endpoint_id' => $this->endpoint->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'event_type' => 'user.created',
        'payload' => ['test' => true],
        'status' => 'success',
        'attempts' => 1,
    ]);
    WebhookDelivery::where('id', $success->id)->update(['created_at' => now()->subHours(2)]);

    $failed = WebhookDelivery::create([
        'webhook_endpoint_id' => $this->endpoint->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'event_type' => 'user.created',
        'payload' => ['test' => true],
        'status' => 'failed',
        'attempts' => 3,
    ]);
    WebhookDelivery::where('id', $failed->id)->update(['created_at' => now()->subHours(2)]);

    $this->artisan('webhooks:prune-stale')->assertExitCode(0);

    expect($success->fresh()->status)->toBe('success');
    expect($failed->fresh()->status)->toBe('failed');
});
