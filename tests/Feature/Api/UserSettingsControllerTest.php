<?php

use App\Models\User;

beforeEach(function () {
    config(['features.user_settings.enabled' => true]);
});

it('requires authentication for index', function () {
    $response = $this->getJson('/api/settings');

    $response->assertUnauthorized();
});

it('returns default settings for new user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/settings');

    $response->assertOk()
        ->assertJson([
            'theme' => 'system',
            'timezone' => config('app.timezone'),
        ]);
});

it('requires authentication for store', function () {
    $response = $this->postJson('/api/settings', [
        'key' => 'theme',
        'value' => 'dark',
    ]);

    $response->assertUnauthorized();
});

it('stores a valid setting', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings', [
            'key' => 'theme',
            'value' => 'dark',
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('user_settings', [
        'user_id' => $user->id,
        'key' => 'theme',
        'value' => 'dark',
    ]);
});

it('rejects unknown keys', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings', [
            'key' => 'malicious',
            'value' => 'anything',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['key']);
});

it('rejects oversized values', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings', [
            'key' => 'theme',
            'value' => str_repeat('a', 2000),
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['value']);
});

it('rejects missing key', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings', [
            'value' => 'dark',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['key']);
});

it('rejects missing value', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings', [
            'key' => 'theme',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['value']);
});
