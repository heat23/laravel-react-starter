<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin/config')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/config')->assertStatus(403);
});

it('loads config page with feature flags', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/config');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Config')
        ->has('feature_flags')
    );
});

it('loads config page with environment settings', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/config');

    $response->assertInertia(fn ($page) => $page
        ->has('environment')
        ->has('environment.app_env')
        ->has('environment.timezone')
    );
});

it('does not expose secrets in environment data', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/config');

    $response->assertInertia(fn ($page) => $page
        ->missing('environment.stripe_secret')
        ->missing('environment.app_key')
        ->missing('environment.db_password')
    );
});

it('feature flags include key, enabled, and env_var', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/config');

    $response->assertInertia(fn ($page) => $page
        ->has('feature_flags.0.key')
        ->has('feature_flags.0.enabled')
        ->has('feature_flags.0.env_var')
    );
});

it('feature flags env_var follows FEATURE_ convention', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/config');

    $response->assertInertia(function ($page) {
        $flags = $page->toArray()['props']['feature_flags'];
        foreach ($flags as $flag) {
            expect($flag['env_var'])->toStartWith('FEATURE_');
            expect($flag['env_var'])->toBe('FEATURE_'.strtoupper($flag['key']));
        }
    });
});

it('only includes minimal environment settings', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/config');

    $response->assertInertia(fn ($page) => $page
        ->has('environment.app_env')
        ->has('environment.timezone')
        ->missing('environment.app_debug')
        ->missing('environment.cache_driver')
        ->missing('environment.queue_driver')
        ->missing('environment.session_driver')
        ->missing('environment.mail_driver')
        ->missing('environment.database_driver')
        ->missing('environment.log_channel')
        ->missing('environment.app_url')
    );
});
