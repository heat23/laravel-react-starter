<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    config(['features.api_tokens.enabled' => true]);
});

// ---------------------------------------------------------------------------
// GET /api/tokens — requires 'read' ability
// ---------------------------------------------------------------------------

test('read-ability token can list tokens', function () {
    $user = User::factory()->create();
    $user->createToken('existing');
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/tokens')
        ->assertOk()
        ->assertJsonStructure([['id', 'name', 'abilities']]);
});

test('write-only token cannot list tokens', function () {
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/tokens')
        ->assertForbidden();
});

test('delete-only token cannot list tokens', function () {
    $user = User::factory()->create();
    $token = $user->createToken('deleter', ['delete']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/tokens')
        ->assertForbidden();
});

test('session-authenticated user can list tokens', function () {
    $user = User::factory()->create();
    $user->createToken('existing');

    Sanctum::actingAs($user, ['*']);

    $this->getJson('/api/tokens')
        ->assertOk()
        ->assertJsonCount(1);
});

// ---------------------------------------------------------------------------
// POST /api/tokens — requires 'write' ability
// ---------------------------------------------------------------------------

test('write-ability token can create a token', function () {
    config(['plans.free.limits.api_tokens' => null]); // Remove limit so plan cap doesn't interfere
    $user = User::factory()->create();
    $token = $user->createToken('writer', ['write']);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/tokens', ['name' => 'New Token', 'abilities' => ['read']])
        ->assertCreated()
        ->assertJsonStructure(['token', 'id']);
});

test('read-only token cannot create a token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->postJson('/api/tokens', ['name' => 'New Token', 'abilities' => ['read']])
        ->assertForbidden();
});

test('session-authenticated user can create a token', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->postJson('/api/tokens', ['name' => 'New Token', 'abilities' => ['read']])
        ->assertCreated()
        ->assertJsonStructure(['token', 'id']);
});

// ---------------------------------------------------------------------------
// DELETE /api/tokens/{id} — requires 'delete' ability
// ---------------------------------------------------------------------------

test('delete-ability token can delete a token', function () {
    $user = User::factory()->create();
    $existing = $user->createToken('target');
    $targetId = $existing->accessToken->id;
    $token = $user->createToken('deleter', ['delete']);

    $this->withToken($token->plainTextToken)
        ->deleteJson("/api/tokens/{$targetId}")
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $targetId]);
});

test('read-only token cannot delete a token', function () {
    $user = User::factory()->create();
    $existing = $user->createToken('target');
    $targetId = $existing->accessToken->id;
    $token = $user->createToken('reader', ['read']);

    $this->withToken($token->plainTextToken)
        ->deleteJson("/api/tokens/{$targetId}")
        ->assertForbidden();

    $this->assertDatabaseHas('personal_access_tokens', ['id' => $targetId]);
});

test('session-authenticated user can delete a token', function () {
    $user = User::factory()->create();
    $existing = $user->createToken('target');
    $targetId = $existing->accessToken->id;

    Sanctum::actingAs($user, ['*']);

    $this->deleteJson("/api/tokens/{$targetId}")
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $targetId]);
});

// ---------------------------------------------------------------------------
// Token creation defaults to 'read' ability
// ---------------------------------------------------------------------------

test('token defaults to read ability when abilities not specified', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->postJson('/api/tokens', ['name' => 'Default Abilities Token'])
        ->assertCreated();

    $created = $user->tokens()->where('name', 'Default Abilities Token')->first();
    expect($created->abilities)->toBe(['read']);
});
