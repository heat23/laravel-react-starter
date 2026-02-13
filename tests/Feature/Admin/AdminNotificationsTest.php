<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.notifications.enabled' => true]);
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin/notifications')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/notifications')->assertStatus(403);
});

it('loads notifications dashboard with stats', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/notifications');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Notifications/Dashboard')
        ->has('stats')
        ->where('stats.total_sent', 0)
        ->where('stats.unread', 0)
        ->where('stats.read_rate', 0)
        ->has('volume_chart')
    );
});

it('counts notifications correctly', function () {
    $admin = User::factory()->admin()->create();

    DB::table('notifications')->insert([
        'id' => Illuminate\Support\Str::uuid(),
        'type' => 'App\\Notifications\\TestNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $admin->id,
        'data' => json_encode(['message' => 'test']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/notifications');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_sent', 1)
        ->where('stats.unread', 1)
        ->where('stats.read_rate', 0)
    );
});

it('calculates read rate', function () {
    $admin = User::factory()->admin()->create();

    DB::table('notifications')->insert([
        'id' => Illuminate\Support\Str::uuid(),
        'type' => 'App\\Notifications\\TestNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $admin->id,
        'data' => json_encode(['message' => 'test']),
        'read_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/notifications');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_sent', 1)
        ->where('stats.read', 1)
        ->where('stats.read_rate', 100)
    );
});
