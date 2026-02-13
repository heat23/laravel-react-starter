<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertStatus(403);
});

it('loads with stats data', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Dashboard')
        ->has('stats')
        ->where('stats.total_users', 4) // 3 + admin
        ->where('stats.admin_count', 1)
    );
});

it('loads signup chart data', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertInertia(fn ($page) => $page
        ->has('signup_chart')
    );
});

it('loads recent activity', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $admin->id,
        'ip' => '127.0.0.1',
        'metadata' => ['email' => $admin->email],
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertInertia(fn ($page) => $page
        ->has('recent_activity', 1)
        ->where('recent_activity.0.event', 'auth.login')
    );
});

it('includes new_users_7d and new_users_30d in stats', function () {
    $admin = User::factory()->admin()->create();

    // Create user from 15 days ago (outside 7d, inside 30d)
    $oldUser = User::factory()->create();
    $oldUser->forceFill(['created_at' => now()->subDays(15)])->saveQuietly();

    // Create recent user (inside 7d)
    User::factory()->create();

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.new_users_7d', 2) // admin + recent user
        ->where('stats.new_users_30d', 3) // all 3 users
    );
});

it('handles empty state with no users except admin', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_users', 1)
        ->where('stats.admin_count', 1)
        ->has('recent_activity', 0)
    );
});

it('limits recent activity to 15 items', function () {
    $admin = User::factory()->admin()->create();
    for ($i = 0; $i < 20; $i++) {
        AuditLog::create([
            'event' => 'test.event',
            'user_id' => $admin->id,
            'ip' => '127.0.0.1',
            'metadata' => [],
        ]);
    }

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertInertia(fn ($page) => $page
        ->has('recent_activity', 15)
    );
});

it('recent activity includes user data via eager loading', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $admin->id,
        'ip' => '192.168.1.1',
        'metadata' => [],
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertInertia(fn ($page) => $page
        ->where('recent_activity.0.user_name', $admin->name)
        ->where('recent_activity.0.user_email', $admin->email)
        ->where('recent_activity.0.ip', '192.168.1.1')
    );
});

it('dashboard query count does not scale with data volume', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(10)->create();
    for ($i = 0; $i < 20; $i++) {
        AuditLog::create(['event' => 'test.event', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    }

    Cache::flush();

    DB::enableQueryLog();
    $this->actingAs($admin)->get('/admin');
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Should be constant: auth + cached stats + cached chart + recent activity (eager loaded)
    expect($queryCount)->toBeLessThan(20);
});
