<?php

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

// ---------------------------------------------------------------------------
// GET /api/user — requires 'read' ability
// ---------------------------------------------------------------------------

test('read-ability token can access /api/user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonStructure(['id', 'name', 'email']);
});

test('write-only token cannot access /api/user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/user')
        ->assertForbidden();
});

test('session-authenticated user can access /api/user without explicit token ability', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->getJson('/api/user')
        ->assertOk();
});

// ---------------------------------------------------------------------------
// GET /api/settings — requires 'read' ability
// ---------------------------------------------------------------------------

test('read-ability token can GET settings', function () {
    config(['features.user_settings.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/settings')
        ->assertOk();
});

test('write-only token cannot GET settings', function () {
    config(['features.user_settings.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/settings')
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// POST /api/settings — requires 'write' ability
// ---------------------------------------------------------------------------

test('write-ability token can POST settings', function () {
    config(['features.user_settings.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/settings', ['key' => 'theme', 'value' => 'dark'])
        ->assertOk();
});

test('read-only token cannot POST settings', function () {
    config(['features.user_settings.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/settings', ['key' => 'theme', 'value' => 'dark'])
        ->assertForbidden();
});

test('session-authenticated user can POST settings without explicit write ability', function () {
    config(['features.user_settings.enabled' => true]);
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->postJson('/api/settings', ['key' => 'theme', 'value' => 'dark'])
        ->assertOk();
});

// ---------------------------------------------------------------------------
// GET /api/notifications — requires 'read' ability
// ---------------------------------------------------------------------------

test('read-ability token can GET notifications', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/notifications')
        ->assertOk();
});

test('write-only token cannot GET notifications', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/notifications')
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// POST /api/notifications/read-all — requires 'write' ability
// ---------------------------------------------------------------------------

test('write-ability token can mark all notifications read', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/notifications/read-all')
        ->assertOk();
});

test('read-only token cannot mark all notifications read', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/notifications/read-all')
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// PATCH /api/notifications/{id}/read — requires 'write' ability
// ---------------------------------------------------------------------------

test('write-ability token can mark a single notification read', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\UpgradeNudgeNotification',
        'data' => ['type' => 'upgrade_nudge', 'score' => 50],
        'read_at' => null,
    ]);
    $notificationId = $user->notifications()->first()->id;
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->patchJson("/api/notifications/{$notificationId}/read")
        ->assertOk();
});

test('read-only token cannot mark a single notification read', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    // Middleware ability check fires before controller; fake ID is sufficient to test 403
    $this->withToken($token->plainTextToken)
        ->patchJson('/api/notifications/'.Str::uuid().'/read')
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// DELETE /api/notifications/{id} — requires 'delete' ability
// ---------------------------------------------------------------------------

test('delete-ability token can delete a notification', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    // Seed a real database notification record directly to avoid passing an
    // Eloquent model (DatabaseNotification) to notify() which expects a
    // Notification subclass and would throw a TypeError.
    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\UpgradeNudgeNotification',
        'data' => ['type' => 'upgrade_nudge', 'score' => 50],
        'read_at' => null,
    ]);
    $notificationId = $user->notifications()->first()->id;
    $token = $user->createToken('deleter', ['delete']);

    $this->withToken($token->plainTextToken)
        ->deleteJson("/api/notifications/{$notificationId}")
        ->assertOk()
        ->assertJson(['message' => 'Notification deleted']);
});

test('read-only token cannot delete a notification', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->deleteJson('/api/notifications/fake-id')
        ->assertForbidden();
});

test('session-authenticated user can access all notification routes', function () {
    config(['features.notifications.enabled' => true]);
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->getJson('/api/notifications')->assertOk();
    $this->postJson('/api/notifications/read-all')->assertOk();
});

// ---------------------------------------------------------------------------
// Star (*) token passes all ability checks
// ---------------------------------------------------------------------------

test('wildcard ability token passes all ability checks', function () {
    config(['features.user_settings.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('supertoken', ['*']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/user')
        ->assertOk();

    $this->withToken($token->plainTextToken)
        ->getJson('/api/settings')
        ->assertOk();
});

// ---------------------------------------------------------------------------
// Webhook scope enforcement — GET requires 'read', POST/PATCH require 'write',
// DELETE requires 'delete'
// ---------------------------------------------------------------------------

test('read-ability token can GET /api/webhooks', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/webhooks')
        ->assertOk();
});

test('write-only token cannot GET /api/webhooks', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/webhooks')
        ->assertForbidden();
});

test('read-ability token can GET /api/webhooks/{id}/deliveries', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->getJson("/api/webhooks/{$endpoint->id}/deliveries")
        ->assertOk();
});

test('write-only token cannot GET /api/webhooks/{id}/deliveries', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    // Middleware ability check fires before controller; fake ID is sufficient to test 403
    $this->withToken($token->plainTextToken)
        ->getJson('/api/webhooks/999/deliveries')
        ->assertForbidden();
});

test('read-only token cannot POST /api/webhooks', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/webhooks', ['url' => 'https://example.com', 'events' => ['user.created']])
        ->assertForbidden();
});

test('read-only token cannot PATCH /api/webhooks/{id}', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->patchJson('/api/webhooks/999', ['active' => false])
        ->assertForbidden();
});

test('read-only token cannot DELETE /api/webhooks/{id}', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->deleteJson('/api/webhooks/999')
        ->assertForbidden();
});

test('write-ability token cannot DELETE /api/webhooks/{id}', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    // write cannot delete — requires explicit 'delete' ability
    $this->withToken($token->plainTextToken)
        ->deleteJson('/api/webhooks/999')
        ->assertForbidden();
});

test('wildcard token can reach all webhook routes', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);
    $token = $user->createToken('supertoken', ['*']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/webhooks')
        ->assertOk();

    $this->withToken($token->plainTextToken)
        ->getJson("/api/webhooks/{$endpoint->id}/deliveries")
        ->assertOk();

    $this->withToken($token->plainTextToken)
        ->getJson("/api/webhooks/{$endpoint->id}")
        ->assertOk();
});

// ---------------------------------------------------------------------------
// POST /api/webhooks/{endpointId}/test — requires 'write' ability
// ---------------------------------------------------------------------------

test('read-only token cannot POST /api/webhooks/{id}/test', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    // Middleware ability check fires before controller; fake ID is sufficient to test 403
    $this->withToken($token->plainTextToken)
        ->postJson('/api/webhooks/999/test')
        ->assertForbidden();
});

test('write-ability token is not scope-rejected on POST /api/webhooks/{id}/test', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);
    $token = $user->createToken('writer', ['write']);

    // Ability check passes; downstream may return 2xx/4xx depending on feature state
    // but must not be 403 (scope-enforcement rejection).
    $response = $this->withToken($token->plainTextToken)
        ->postJson("/api/webhooks/{$endpoint->id}/test");

    expect($response->status())->not->toBe(403);
});

// Token management scope enforcement (GET/POST/DELETE /api/tokens) is covered
// exhaustively in tests/Feature/Api/TokenAbilityTest.php, which was introduced
// specifically for that surface. Tests there use the correct plan-limit bypass
// (config(['plans.free.limits.api_tokens' => null])) so they remain accurate.
