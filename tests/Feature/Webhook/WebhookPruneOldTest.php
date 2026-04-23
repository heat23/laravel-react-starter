<?php

use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;

it('deletes old terminal webhook deliveries', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);

    // Old terminal deliveries (should be deleted)
    $oldDelivered = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'status' => 'success',
        'created_at' => now()->subDays(100),
    ]);
    $oldFailed = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'status' => 'failed',
        'created_at' => now()->subDays(100),
    ]);
    $oldAbandoned = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'status' => 'abandoned',
        'created_at' => now()->subDays(100),
    ]);

    // Recent delivery (should be preserved)
    $recentDelivery = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'status' => 'success',
        'created_at' => now()->subDays(10),
    ]);

    // Old pending delivery (should be preserved - not terminal)
    $oldPending = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'status' => 'pending',
        'created_at' => now()->subDays(100),
    ]);

    $this->artisan('webhooks:delete-old')
        ->expectsOutputToContain('Pruned 3 old webhook deliveries')
        ->assertSuccessful();

    expect(WebhookDelivery::find($oldDelivered->id))->toBeNull();
    expect(WebhookDelivery::find($oldFailed->id))->toBeNull();
    expect(WebhookDelivery::find($oldAbandoned->id))->toBeNull();
    expect(WebhookDelivery::find($recentDelivery->id))->not->toBeNull();
    expect(WebhookDelivery::find($oldPending->id))->not->toBeNull();
});

it('accepts custom retention days', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);

    $delivery = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'status' => 'success',
        'created_at' => now()->subDays(40),
    ]);

    $this->artisan('webhooks:delete-old --days=30')
        ->assertSuccessful();

    expect(WebhookDelivery::find($delivery->id))->toBeNull();
});

it('does nothing when no old deliveries exist', function () {
    $this->artisan('webhooks:delete-old')
        ->expectsOutputToContain('Pruned 0 old webhook deliveries')
        ->assertSuccessful();
});
