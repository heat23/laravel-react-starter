<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);
    registerAdminRoutes();
    ensureTwoFactorTableExists();
});

function ensureTwoFactorTableExists(): void
{
    if (! Schema::hasTable('two_factor_authentications')) {
        Schema::create('two_factor_authentications', function ($table) {
            $table->id();
            $table->morphs('authenticatable');
            $table->text('shared_secret');
            $table->timestampTz('enabled_at')->nullable();
            $table->string('label');
            $table->unsignedTinyInteger('digits')->default(6);
            $table->unsignedTinyInteger('seconds')->default(30);
            $table->unsignedTinyInteger('window')->default(0);
            $table->string('algorithm', 16)->default('sha1');
            $table->text('recovery_codes')->nullable();
            $table->timestampTz('recovery_codes_generated_at')->nullable();
            $table->timestampsTz();
        });
    }
}

it('redirects guests to login', function () {
    $this->get('/admin/two-factor')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/two-factor')->assertStatus(403);
});

it('loads two-factor dashboard with stats', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/two-factor');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/TwoFactor/Dashboard')
        ->has('stats')
        ->where('stats.total_users', 1)
        ->where('stats.two_factor_enabled', 0)
        ->where('stats.adoption_rate', 0)
    );
});

it('counts 2fa enabled users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    DB::table('two_factor_authentications')->insert([
        'authenticatable_type' => 'App\\Models\\User',
        'authenticatable_id' => $user->id,
        'shared_secret' => encrypt('secret'),
        'enabled_at' => now(),
        'label' => $user->email,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/two-factor');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_users', 2)
        ->where('stats.two_factor_enabled', 1)
        ->where('stats.adoption_rate', 50)
        ->where('stats.without_two_factor', 1)
    );
});
