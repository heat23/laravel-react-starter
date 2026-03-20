<?php

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
