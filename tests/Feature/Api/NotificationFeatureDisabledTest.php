<?php

use App\Models\User;

/**
 * Feature flag stays at default (false) — all notification endpoints should return 404.
 */
test('returns 404 for list when feature is disabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/notifications')
        ->assertNotFound();
});

test('returns 404 for mark as read when feature is disabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/api/notifications/fake-id/read')
        ->assertNotFound();
});

test('returns 404 for mark all as read when feature is disabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/notifications/read-all')
        ->assertNotFound();
});

test('returns 404 for delete when feature is disabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->deleteJson('/api/notifications/fake-id')
        ->assertNotFound();
});
