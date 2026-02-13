<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['features.social_auth.enabled' => true]);
    registerAdminRoutes();
    ensureSocialAccountsTableExists();
});

function ensureSocialAccountsTableExists(): void
{
    if (! Schema::hasTable('social_accounts')) {
        Schema::create('social_accounts', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('provider', 32);
            $table->string('provider_id');
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_id']);
        });
    }
}

it('redirects guests to login', function () {
    $this->get('/admin/social-auth')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/social-auth')->assertStatus(403);
});

it('loads social auth dashboard with stats', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/social-auth');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/SocialAuth/Dashboard')
        ->has('stats')
        ->where('stats.total_linked', 0)
        ->where('stats.users_with_social', 0)
    );
});

it('counts linked accounts by provider', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    DB::table('social_accounts')->insert([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_id' => 'google_123',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/social-auth');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_linked', 1)
        ->where('stats.users_with_social', 1)
        ->where('stats.by_provider.google', 1)
    );
});
