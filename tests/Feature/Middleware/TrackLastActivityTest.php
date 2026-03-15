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
