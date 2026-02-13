<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.api_tokens.enabled' => true]);
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin/tokens')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/tokens')->assertStatus(403);
});

it('loads tokens dashboard with stats', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/tokens');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Tokens/Dashboard')
        ->has('stats')
        ->where('stats.total_tokens', 0)
        ->where('stats.users_with_tokens', 0)
        ->has('most_active')
    );
});

it('counts tokens correctly', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->createToken('Test Token');

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/tokens');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_tokens', 1)
        ->where('stats.users_with_tokens', 1)
        ->where('stats.never_used', 1)
    );
});

it('shows most active tokens', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $token = $user->createToken('Active Token');

    DB::table('personal_access_tokens')
        ->where('id', $token->accessToken->id)
        ->update(['last_used_at' => now()]);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/tokens');

    $response->assertInertia(fn ($page) => $page
        ->has('most_active', 1)
        ->where('most_active.0.token_name', 'Active Token')
        ->where('most_active.0.user_name', $user->name)
    );
});
