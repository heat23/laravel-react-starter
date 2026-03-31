<?php

use App\Jobs\BroadcastAnnouncementJob;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
        'id' => Str::uuid(),
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
        'id' => Str::uuid(),
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

// ── send() ────────────────────────────────────────────────────────────────────

it('blocks non-super-admin from sending announcements', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);

    $this->actingAs($admin)
        ->post('/admin/notifications/send', [
            'subject' => 'Hello',
            'body' => 'Body text here.',
            'recipient' => 'all',
        ])
        ->assertForbidden();
});

it('super admin can send announcement to all users', function () {
    Bus::fake();

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/notifications/send', [
            'subject' => 'Hello everyone',
            'body' => 'This is a test announcement.',
            'recipient' => 'all',
        ])
        ->assertRedirect();

    Bus::assertDispatched(BroadcastAnnouncementJob::class, function ($job) {
        return $job->recipient === 'all'
            && $job->subject === 'Hello everyone'
            && $job->body === 'This is a test announcement.';
    });
});

it('super admin can send announcement to admins only', function () {
    Bus::fake();

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/notifications/send', [
            'subject' => 'Admin notice',
            'body' => 'For admins only.',
            'recipient' => 'admins',
        ])
        ->assertRedirect();

    Bus::assertDispatched(BroadcastAnnouncementJob::class, function ($job) {
        return $job->recipient === 'admins';
    });
});

it('rejects send with missing subject', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/notifications/send', [
            'body' => 'Body text here.',
            'recipient' => 'all',
        ])
        ->assertSessionHasErrors('subject');
});

it('rejects send with invalid recipient', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/notifications/send', [
            'subject' => 'Hello',
            'body' => 'Body text here.',
            'recipient' => 'invalid',
        ])
        ->assertSessionHasErrors('recipient');
});

it('send invalidates notification caches', function () {
    Bus::fake();
    Cache::flush();

    $admin = User::factory()->superAdmin()->create();

    Cache::put('admin:notifications:stats', ['stale' => true], 300);
    Cache::put('admin:notifications:volume', ['stale' => true], 300);

    $this->actingAs($admin)
        ->post('/admin/notifications/send', [
            'subject' => 'Cache bust test',
            'body' => 'Body text.',
            'recipient' => 'all',
        ]);

    expect(Cache::has('admin:notifications:stats'))->toBeFalse();
    expect(Cache::has('admin:notifications:volume'))->toBeFalse();
});
