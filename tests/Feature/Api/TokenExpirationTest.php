<?php

use App\Models\User;

beforeEach(function () {
    config(['features.api_tokens.enabled' => true]);
});

test('create token with expiration date', function () {
    $user = User::factory()->create();
    $expiresAt = now()->addDays(30)->toISOString();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/tokens', [
            'name' => 'Expiring Token',
            'expires_at' => $expiresAt,
        ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'id']);

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => 'Expiring Token',
    ]);

    $token = $user->tokens()->where('name', 'Expiring Token')->first();
    $this->assertNotNull($token->expires_at);
});

test('create token without expiration', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/tokens', [
            'name' => 'No Expiry Token',
        ]);

    $response->assertOk();

    $token = $user->tokens()->where('name', 'No Expiry Token')->first();
    $this->assertNull($token->expires_at);
});

test('expires at must be in the future', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/tokens', [
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

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/tokens');

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

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/tokens', [
            'name' => 'Normal Token',
            'abilities' => ['read'],
        ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'id']);
});
