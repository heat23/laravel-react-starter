<?php

use App\Models\User;

it('admin can view the sessions page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/sessions')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Sessions/Index'));
});

it('admin can terminate user sessions', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/sessions/{$user->id}")
        ->assertRedirect('/admin/sessions');
});

it('regular admin cannot terminate sessions', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/sessions/{$user->id}")
        ->assertForbidden();
});
