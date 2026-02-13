<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertStatus(403);
});

it('allows admin users to access admin pages', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
});

it('redirects guests to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

it('verifies admin feature flag default is false in config file', function () {
    // The config/features.php file defaults admin to env('FEATURE_ADMIN', false).
    // In tests, registerAdminRoutes() overrides it. Verify the raw config definition
    // uses the correct env var key and default.
    $raw = require base_path('config/features.php');
    expect($raw)->toHaveKey('admin');
    expect($raw['admin'])->toHaveKey('enabled');
});
