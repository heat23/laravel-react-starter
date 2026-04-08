<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    config()->set('features.user_settings.enabled', true);
});

it('accepts sidebar_state as valid key', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->postJson('/api/settings', [
        'key' => 'sidebar_state',
        'value' => 'collapsed',
    ])
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('accepts onboarding_completed as valid key', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->postJson('/api/settings', [
        'key' => 'onboarding_completed',
        'value' => '2024-01-01',
    ])
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('rejects non-string values', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->postJson('/api/settings', [
        'key' => 'theme',
        'value' => ['dark'],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['value']);
});
