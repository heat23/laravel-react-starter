<?php

use App\Models\User;

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
    $target = User::factory()->create();
    $this->actingAs($user)
        ->patch("/admin/users/{$target->id}/toggle-admin")
        ->assertForbidden();
});
