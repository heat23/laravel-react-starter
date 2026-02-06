<?php

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

function createNotification(User $user, array $data = [], bool $read = false): DatabaseNotification
{
    return $user->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\\Notifications\\TestNotification',
        'data' => array_merge([
            'title' => 'Test notification',
            'message' => 'This is a test notification',
        ], $data),
        'read_at' => $read ? now() : null,
    ]);
}

beforeEach(function () {
    config(['features.notifications.enabled' => true]);
});

test('lists user notifications paginated', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 25; $i++) {
        createNotification($user, ['title' => "Notification {$i}"]);
    }

    $response = $this->actingAs($user)
        ->getJson('/api/notifications');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'type', 'data', 'read_at', 'created_at']],
            'current_page',
            'last_page',
            'per_page',
            'total',
        ])
        ->assertJsonPath('per_page', 20)
        ->assertJsonPath('total', 25)
        ->assertJsonCount(20, 'data');
});

test('does not list other users notifications', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    createNotification($user, ['title' => 'My notification']);
    createNotification($other, ['title' => 'Other notification']);

    $response = $this->actingAs($user)
        ->getJson('/api/notifications');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.data.title', 'My notification');
});

test('marks a notification as read', function () {
    $user = User::factory()->create();
    $notification = createNotification($user);

    expect($notification->read_at)->toBeNull();

    $response = $this->actingAs($user)
        ->patchJson("/api/notifications/{$notification->id}/read");

    $response->assertOk();
    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('marks all notifications as read', function () {
    $user = User::factory()->create();
    createNotification($user);
    createNotification($user);
    createNotification($user);

    expect($user->unreadNotifications()->count())->toBe(3);

    $response = $this->actingAs($user)
        ->postJson('/api/notifications/read-all');

    $response->assertOk();
    expect($user->unreadNotifications()->count())->toBe(0);
});

test('deletes a notification', function () {
    $user = User::factory()->create();
    $notification = createNotification($user);

    $response = $this->actingAs($user)
        ->deleteJson("/api/notifications/{$notification->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
});

test('returns 404 for nonexistent notification', function () {
    $user = User::factory()->create();
    $fakeId = Str::uuid()->toString();

    $response = $this->actingAs($user)
        ->patchJson("/api/notifications/{$fakeId}/read");

    $response->assertNotFound();
});

test('returns 404 when marking another users notification as read', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $notification = createNotification($other);

    $response = $this->actingAs($user)
        ->patchJson("/api/notifications/{$notification->id}/read");

    $response->assertNotFound();
});

test('returns 404 when deleting another users notification', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $notification = createNotification($other);

    $this->actingAs($user)
        ->deleteJson("/api/notifications/{$notification->id}")
        ->assertNotFound();
});

test('marking already read notification as read is idempotent', function () {
    $user = User::factory()->create();
    $notification = createNotification($user, [], read: true);

    $this->actingAs($user)
        ->patchJson("/api/notifications/{$notification->id}/read")
        ->assertOk();
});

test('unauthenticated user cannot list notifications', function () {
    $this->getJson('/api/notifications')
        ->assertUnauthorized();
});

test('unauthenticated user cannot mark notification as read', function () {
    $this->patchJson('/api/notifications/fake-id/read')
        ->assertUnauthorized();
});

test('unauthenticated user cannot mark all as read', function () {
    $this->postJson('/api/notifications/read-all')
        ->assertUnauthorized();
});

test('unauthenticated user cannot delete notification', function () {
    $this->deleteJson('/api/notifications/fake-id')
        ->assertUnauthorized();
});
