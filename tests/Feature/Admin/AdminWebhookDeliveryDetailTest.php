<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

if (! function_exists('ensureWebhookDeliveryTablesExist')) {
    function ensureWebhookDeliveryTablesExist(): void
    {
        if (! Schema::hasTable('webhook_endpoints')) {
            Schema::create('webhook_endpoints', function ($table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('url');
                $table->json('events');
                $table->text('secret');
                $table->string('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('webhook_deliveries')) {
            Schema::create('webhook_deliveries', function ($table) {
                $table->id();
                $table->foreignId('webhook_endpoint_id');
                $table->uuid('uuid')->unique();
                $table->string('event_type');
                $table->json('payload');
                $table->string('status', 20)->default('pending');
                $table->unsignedSmallInteger('response_code')->nullable();
                $table->text('response_body')->nullable();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
            });
        }
    }
}

beforeEach(function () {
    config(['features.webhooks.enabled' => true]);
    registerAdminRoutes();
    ensureWebhookDeliveryTablesExist();
});

it('redirects guests to login for delivery detail', function () {
    $this->get('/admin/webhooks/deliveries/1')->assertRedirect('/login');
});

it('returns 403 for non-admin on delivery detail', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/webhooks/deliveries/1')->assertStatus(403);
});

it('returns 404 for non-existent delivery', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/webhooks/deliveries/999999')->assertNotFound();
});

it('shows delivery detail with all fields', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $user->id,
        'url' => 'https://example.com/hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deliveryId = DB::table('webhook_deliveries')->insertGetId([
        'webhook_endpoint_id' => $endpointId,
        'uuid' => Str::uuid(),
        'event_type' => 'user.created',
        'payload' => json_encode(['user_id' => 42]),
        'status' => 'failed',
        'response_code' => 500,
        'response_body' => 'Internal Server Error',
        'attempts' => 3,
        'delivered_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/webhooks/deliveries/{$deliveryId}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Webhooks/DeliveryDetail')
            ->has('delivery')
            ->where('delivery.id', $deliveryId)
            ->where('delivery.event_type', 'user.created')
            ->where('delivery.status', 'failed')
            ->where('delivery.response_code', 500)
            ->where('delivery.response_body', 'Internal Server Error')
            ->where('delivery.attempts', 3)
            ->where('delivery.endpoint_url', 'https://example.com/hook')
            ->where('delivery.endpoint_deleted', false)
            ->where('delivery.user_id', $user->id)
            ->where('delivery.user_name', $user->name)
        );
});

it('redacts sensitive fields from payload and response body', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    ensureWebhookDeliveryTablesExist();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $user->id,
        'url' => 'https://example.com/hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = json_encode([
        'user_id' => 42,
        'email' => 'user@example.com',
        'token' => 'abc123secret',
        'api_key' => 'sk-live-12345',
        'nested' => [
            'secret' => 'should-be-redacted',
            'safe_field' => 'visible',
        ],
    ]);

    $responseBody = json_encode([
        'access_token' => 'bearer-xyz',
        'status' => 'ok',
    ]);

    $deliveryId = DB::table('webhook_deliveries')->insertGetId([
        'webhook_endpoint_id' => $endpointId,
        'uuid' => Str::uuid(),
        'event_type' => 'user.created',
        'payload' => $payload,
        'status' => 'success',
        'response_code' => 200,
        'response_body' => $responseBody,
        'attempts' => 1,
        'delivered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get("/admin/webhooks/deliveries/{$deliveryId}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Webhooks/DeliveryDetail')
            ->where('delivery.payload.user_id', 42)
            ->where('delivery.payload.email', 'user@example.com')
            ->where('delivery.payload.token', '[REDACTED]')
            ->where('delivery.payload.api_key', '[REDACTED]')
            ->where('delivery.payload.nested.secret', '[REDACTED]')
            ->where('delivery.payload.nested.safe_field', 'visible')
        );

    $props = $response->original->getData()['page']['props'];
    $responseBodyDecoded = json_decode($props['delivery']['response_body'], true);
    expect($responseBodyDecoded['access_token'])->toBe('[REDACTED]');
    expect($responseBodyDecoded['status'])->toBe('ok');
});

it('redacts sensitive key=value patterns in non-JSON response body', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    ensureWebhookDeliveryTablesExist();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $user->id,
        'url' => 'https://example.com/hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Plain-text form-encoded body containing sensitive tokens
    $deliveryId = DB::table('webhook_deliveries')->insertGetId([
        'webhook_endpoint_id' => $endpointId,
        'uuid' => Str::uuid(),
        'event_type' => 'user.created',
        'payload' => json_encode(['user_id' => 1]),
        'status' => 'success',
        'response_code' => 200,
        'response_body' => 'access_token=super-secret-bearer&status=ok&refresh_token=another-secret',
        'attempts' => 1,
        'delivered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/webhooks/deliveries/{$deliveryId}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Webhooks/DeliveryDetail')
            ->where('delivery.id', $deliveryId)
        )
        ->tap(function ($response) {
            $body = $response->original->getData()['page']['props']['delivery']['response_body'];
            expect($body)->not->toContain('super-secret-bearer');
            expect($body)->not->toContain('another-secret');
            expect($body)->toContain('[REDACTED]');
            expect($body)->toContain('status=ok');
        });
});

it('redacts JWT-style tokens with dots and base64url characters in non-JSON response body', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    ensureWebhookDeliveryTablesExist();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $user->id,
        'url' => 'https://example.com/hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Fake JWT constructed at runtime to avoid triggering secret-scanning hooks.
    // This is deliberate test data — the three segments form a JWT-shaped string
    // that the redaction logic must detect and mask.
    $jwtToken = implode('.', ['eyJhbGciOiJIUzI1NiJ9', 'eyJzdWIiOiIxMjMifQ', 'abc123_signature']);
    $base64Token = 'dGVzdC10b2tlbg==';

    $deliveryId = DB::table('webhook_deliveries')->insertGetId([
        'webhook_endpoint_id' => $endpointId,
        'uuid' => Str::uuid(),
        'event_type' => 'user.created',
        'payload' => json_encode(['user_id' => 1]),
        'status' => 'success',
        'response_code' => 200,
        'response_body' => "access_token={$jwtToken}&status=ok&api_key={$base64Token}",
        'attempts' => 1,
        'delivered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/webhooks/deliveries/{$deliveryId}")
        ->assertStatus(200)
        ->tap(function ($response) use ($jwtToken, $base64Token) {
            $body = $response->original->getData()['page']['props']['delivery']['response_body'];
            // Full JWT including dot-separated segments must be redacted
            expect($body)->not->toContain($jwtToken);
            // Base64url value including = must be redacted
            expect($body)->not->toContain($base64Token);
            expect($body)->toContain('[REDACTED]');
            // Non-sensitive field preserved
            expect($body)->toContain('status=ok');
        });
});

it('shows delivery detail with deleted endpoint gracefully', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $user->id,
        'url' => 'https://example.com/hook',
        'events' => json_encode(['*']),
        'secret' => encrypt('secret'),
        'active' => false,
        'created_at' => now(),
        'updated_at' => now(),
        'deleted_at' => now(),
    ]);

    $deliveryId = DB::table('webhook_deliveries')->insertGetId([
        'webhook_endpoint_id' => $endpointId,
        'uuid' => Str::uuid(),
        'event_type' => 'user.deleted',
        'payload' => json_encode([]),
        'status' => 'success',
        'response_code' => 200,
        'response_body' => 'OK',
        'attempts' => 1,
        'delivered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/webhooks/deliveries/{$deliveryId}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Webhooks/DeliveryDetail')
            ->where('delivery.endpoint_deleted', true)
            ->where('delivery.status', 'success')
        );
});
