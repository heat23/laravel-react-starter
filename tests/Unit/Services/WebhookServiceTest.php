<?php

use App\Jobs\DispatchWebhookJob;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Queue;

it('generates a webhook secret with correct prefix', function () {
    $service = new WebhookService;

    $secret = $service->generateSecret();

    expect($secret)->toStartWith('whsec_');
    expect(strlen($secret))->toBeGreaterThan(32);
});

it('signs payload with HMAC-SHA256', function () {
    $service = new WebhookService;

    $payload = '{"event":"test"}';
    $secret = 'test-secret';

    $signature = $service->sign($payload, $secret);

    expect($signature)->toBe(hash_hmac('sha256', $payload, $secret));
});

it('produces different signatures for different payloads', function () {
    $service = new WebhookService;

    $sig1 = $service->sign('payload-1', 'secret');
    $sig2 = $service->sign('payload-2', 'secret');

    expect($sig1)->not->toBe($sig2);
});

it('produces different signatures for different secrets', function () {
    $service = new WebhookService;

    $sig1 = $service->sign('payload', 'secret-1');
    $sig2 = $service->sign('payload', 'secret-2');

    expect($sig1)->not->toBe($sig2);
});

it('dispatches webhook to active endpoint subscribed to event', function () {
    Queue::fake();

    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'events' => ['user.created', 'user.updated'],
    ]);

    $service = new WebhookService;
    $service->dispatch($user->id, 'user.created', ['id' => 123, 'name' => 'Test User']);

    expect(WebhookDelivery::count())->toBe(1);

    $delivery = WebhookDelivery::first();
    expect($delivery->webhook_endpoint_id)->toBe($endpoint->id)
        ->and($delivery->event_type)->toBe('user.created')
        ->and($delivery->status)->toBe('pending')
        ->and($delivery->payload)->toBe(['id' => 123, 'name' => 'Test User']);

    Queue::assertPushed(DispatchWebhookJob::class, 1);
});

it('dispatches webhook to multiple endpoints', function () {
    Queue::fake();

    $user = User::factory()->create();
    WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'events' => ['user.created'],
    ]);
    WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'events' => ['user.created', 'user.updated'],
    ]);

    $service = new WebhookService;
    $service->dispatch($user->id, 'user.created', ['id' => 123]);

    expect(WebhookDelivery::count())->toBe(2);
    Queue::assertPushed(DispatchWebhookJob::class, 2);
});

it('does not dispatch to inactive endpoints', function () {
    Queue::fake();

    $user = User::factory()->create();
    WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => false,
        'events' => ['user.created'],
    ]);

    $service = new WebhookService;
    $service->dispatch($user->id, 'user.created', ['id' => 123]);

    expect(WebhookDelivery::count())->toBe(0);
    Queue::assertNotPushed(DispatchWebhookJob::class);
});

it('does not dispatch to endpoints not subscribed to event', function () {
    Queue::fake();

    $user = User::factory()->create();
    WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'events' => ['user.updated', 'user.deleted'],
    ]);

    $service = new WebhookService;
    $service->dispatch($user->id, 'user.created', ['id' => 123]);

    expect(WebhookDelivery::count())->toBe(0);
    Queue::assertNotPushed(DispatchWebhookJob::class);
});

it('does not dispatch to other users endpoints', function () {
    Queue::fake();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    WebhookEndpoint::factory()->create([
        'user_id' => $user2->id,
        'active' => true,
        'events' => ['user.created'],
    ]);

    $service = new WebhookService;
    $service->dispatch($user1->id, 'user.created', ['id' => 123]);

    expect(WebhookDelivery::count())->toBe(0);
    Queue::assertNotPushed(DispatchWebhookJob::class);
});

it('returns early when no matching endpoints exist', function () {
    Queue::fake();

    $user = User::factory()->create();

    $service = new WebhookService;
    $service->dispatch($user->id, 'user.created', ['id' => 123]);

    expect(WebhookDelivery::count())->toBe(0);
    Queue::assertNotPushed(DispatchWebhookJob::class);
});

it('dispatches to endpoint bypassing event filter with dispatchToEndpoint', function () {
    Queue::fake();

    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'events' => ['user.updated'], // Not subscribed to 'test.event'
    ]);

    $service = new WebhookService;
    $service->dispatchToEndpoint($endpoint, 'test.event', ['test' => 'data']);

    expect(WebhookDelivery::count())->toBe(1);

    $delivery = WebhookDelivery::first();
    expect($delivery->webhook_endpoint_id)->toBe($endpoint->id)
        ->and($delivery->event_type)->toBe('test.event')
        ->and($delivery->status)->toBe('pending');

    Queue::assertPushed(DispatchWebhookJob::class, 1);
});

it('creates webhook deliveries with unique UUIDs', function () {
    Queue::fake();

    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'events' => ['user.created'],
    ]);

    $service = new WebhookService;
    $service->dispatch($user->id, 'user.created', ['id' => 1]);
    $service->dispatch($user->id, 'user.created', ['id' => 2]);

    $deliveries = WebhookDelivery::all();
    expect($deliveries->count())->toBe(2);
    expect($deliveries->pluck('uuid')->unique()->count())->toBe(2);
});

it('stores payload as valid JSON', function () {
    Queue::fake();

    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'events' => ['user.created'],
    ]);

    $payload = [
        'id' => 123,
        'name' => 'Test User',
        'nested' => ['key' => 'value'],
        'array' => [1, 2, 3],
    ];

    $service = new WebhookService;
    $service->dispatch($user->id, 'user.created', $payload);

    $delivery = WebhookDelivery::first();

    expect($delivery->payload)->toBe($payload);
});
