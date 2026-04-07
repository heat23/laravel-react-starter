<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    config(['features.api_tokens.enabled' => true]);
});

test('create token with expiration date', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
    $expiresAt = now()->addDays(30)->toISOString();

    $response = $this->postJson('/api/tokens', [
        'name' => 'Expiring Token',
        'expires_at' => $expiresAt,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'id']);

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => 'Expiring Token',
    ]);

    $token = $user->tokens()->where('name', 'Expiring Token')->first();
    $this->assertNotNull($token->expires_at);
});

test('create token without expiration', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/tokens', [
        'name' => 'No Expiry Token',
    ]);

    $response->assertCreated();

    $token = $user->tokens()->where('name', 'No Expiry Token')->first();
    $this->assertNull($token->expires_at);
});

test('expires at must be in the future', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/tokens', [
        'name' => 'Expired Token',
        'expires_at' => now()->subDay()->toISOString(),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['expires_at']);
});

test('index returns expires at for all tokens', function () {
    $user = User::factory()->create();
    $user->createToken('Token 1', ['*'], now()->addDays(30));
    $user->createToken('Token 2');
    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/tokens');

    $response->assertOk()
        ->assertJsonCount(2);

    $tokens = $response->json();
    // One has expires_at set, one is null
    $expiringToken = collect($tokens)->firstWhere('name', 'Token 1');
    $permanentToken = collect($tokens)->firstWhere('name', 'Token 2');

    $this->assertNotNull($expiringToken['expires_at']);
    $this->assertNull($permanentToken['expires_at']);
});

test('existing tests pass without expires at', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/tokens', [
        'name' => 'Normal Token',
        'abilities' => ['read'],
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'id']);
});
