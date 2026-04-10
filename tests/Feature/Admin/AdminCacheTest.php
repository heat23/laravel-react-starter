<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

it('admin can view the cache management page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/cache')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Cache/Index'));
});

it('super admin can flush billing cache', function () {
    $admin = User::factory()->superAdmin()->create();
    Cache::put('admin:billing:stats', ['mrr' => 100], 300);

    $this->actingAs($admin)
        ->post('/admin/cache/flush', ['scope' => 'billing'])
        ->assertRedirect('/admin/cache');

    expect(Cache::has('admin:billing:stats'))->toBeFalse();
});

it('regular admin cannot flush caches', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $this->actingAs($admin)
        ->post('/admin/cache/flush', ['scope' => 'billing'])
        ->assertForbidden();
});

it('rejects invalid scope', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin)
        ->post('/admin/cache/flush', ['scope' => 'invalid_scope'])
        ->assertSessionHasErrors('scope');
});

it('creates an audit log entry when cache is flushed', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/cache/flush', ['scope' => 'billing'])
        ->assertRedirect('/admin/cache');

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.cache_flushed',
        'user_id' => $admin->id,
    ]);

    $log = AuditLog::where('event', 'admin.cache_flushed')->where('user_id', $admin->id)->first();
    expect($log->metadata)->toBeArray();
    expect($log->metadata['scope'])->toBe('billing');
});

it('creates an audit log entry with the correct scope when flushing all caches', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/cache/flush', ['scope' => 'all'])
        ->assertRedirect('/admin/cache');

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.cache_flushed',
        'user_id' => $admin->id,
    ]);

    $log = AuditLog::where('event', 'admin.cache_flushed')->where('user_id', $admin->id)->first();
    expect($log->metadata)->toBeArray();
    expect($log->metadata['scope'])->toBe('all');
});
