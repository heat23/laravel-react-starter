<?php

use App\Models\User;

it('regular admin cannot impersonate a user', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/impersonate")
        ->assertForbidden();
});

it('super admin can impersonate a user', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/impersonate")
        ->assertRedirect();
});

it('regular admin cannot toggle another user admin status', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->patch("/admin/users/{$target->id}/toggle-admin")
        ->assertForbidden();
});

it('super admin can toggle another user admin status', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->patch("/admin/users/{$target->id}/toggle-admin")
        ->assertRedirect();
});

it('regular admin can access the admin panel', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('non-admin cannot access super-admin-gated route', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post('/admin/users/1/impersonate')
        ->assertForbidden();
});
