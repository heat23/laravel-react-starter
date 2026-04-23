<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('super_admin can access users.update', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->patch("/admin/users/{$target->id}", ['name' => $target->name, 'email' => $target->email])
        ->assertRedirect();
});

it('regular admin gets 403 on users.update', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->patch("/admin/users/{$target->id}", ['name' => $target->name, 'email' => $target->email])
        ->assertForbidden();
});

it('guest is redirected to login on users.update', function () {
    $target = User::factory()->create();

    $this->patch("/admin/users/{$target->id}", ['name' => $target->name, 'email' => $target->email])
        ->assertRedirect('/login');
});
