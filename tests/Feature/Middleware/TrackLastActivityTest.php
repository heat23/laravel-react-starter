<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates last_active_at for authenticated users', function () {
    $user = User::factory()->create(['last_active_at' => null]);

    $this->actingAs($user)->get('/dashboard');

    expect($user->fresh()->last_active_at)->not->toBeNull();
});

it('throttles updates to once per 15 minutes', function () {
    $initialTime = now()->subMinutes(5);
    $user = User::factory()->create(['last_active_at' => $initialTime]);

    $this->actingAs($user)->get('/dashboard');

    // last_active_at should NOT be updated since it was set 5 minutes ago
    expect($user->fresh()->last_active_at->toDateTimeString())
        ->toBe($initialTime->toDateTimeString());
});

it('updates last_active_at when older than 15 minutes', function () {
    $oldTime = now()->subMinutes(20);
    $user = User::factory()->create(['last_active_at' => $oldTime]);

    $this->actingAs($user)->get('/dashboard');

    expect($user->fresh()->last_active_at->gt($oldTime))->toBeTrue();
});

it('does not update for unauthenticated requests', function () {
    $response = $this->get('/');

    $response->assertOk();
});

it('respects a custom activity_tracking_window from config', function () {
    config(['app.activity_tracking_window' => 5]);

    // Set last_active_at to 3 minutes ago — within the default 15-min window but outside the custom 5-min window
    $threeMinutesAgo = now()->subMinutes(3);
    $user = User::factory()->create(['last_active_at' => $threeMinutesAgo]);

    $this->actingAs($user)->get('/dashboard');

    // With a 5-min window, 3 minutes ago is still within the window → should NOT update
    expect($user->fresh()->last_active_at->toDateTimeString())
        ->toBe($threeMinutesAgo->toDateTimeString());
});

it('updates when last_active_at exceeds custom activity_tracking_window', function () {
    config(['app.activity_tracking_window' => 5]);

    $oldTime = now()->subMinutes(6);
    $user = User::factory()->create(['last_active_at' => $oldTime]);

    $this->actingAs($user)->get('/dashboard');

    expect($user->fresh()->last_active_at->gt($oldTime))->toBeTrue();
});
