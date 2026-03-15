<?php

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\User;

it('deletes old terminal webhook deliveries', function () {
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($user)->create();

    // Old terminal deliveries (should be deleted)
    $oldDelivered = WebhookDelivery::factory()->for($endpoint)->create([
        'status' => 'success',
        'created_at' => now()->subDays(100),
    ]);
    $oldFailed = WebhookDelivery::factory()->for($endpoint)->create([
        'status' => 'failed',
        'created_at' => now()->subDays(100),
    ]);
    $oldAbandoned = WebhookDelivery::factory()->for($endpoint)->create([
        'status' => 'abandoned',
        'created_at' => now()->subDays(100),
    ]);

    // Recent delivery (should be preserved)
    $recentDelivery = WebhookDelivery::factory()->for($endpoint)->create([
        'status' => 'success',
        'created_at' => now()->subDays(10),
    ]);

    // Old pending delivery (should be preserved - not terminal)
    $oldPending = WebhookDelivery::factory()->for($endpoint)->create([
        'status' => 'pending',
        'created_at' => now()->subDays(100),
    ]);

    $this->artisan('webhooks:prune-old')
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
    $endpoint = WebhookEndpoint::factory()->for($user)->create();

    $delivery = WebhookDelivery::factory()->for($endpoint)->create([
        'status' => 'success',
        'created_at' => now()->subDays(40),
    ]);

    $this->artisan('webhooks:prune-old --days=30')
        ->assertSuccessful();

    expect(WebhookDelivery::find($delivery->id))->toBeNull();
});

it('does nothing when no old deliveries exist', function () {
    $this->artisan('webhooks:prune-old')
        ->expectsOutputToContain('Pruned 0 old webhook deliveries')
        ->assertSuccessful();
});
