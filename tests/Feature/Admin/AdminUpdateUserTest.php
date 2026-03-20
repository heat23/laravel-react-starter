<?php

use App\Models\User;

it('admin can update a user name and email', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $this->actingAs($admin)
        ->patch("/admin/users/{$user->id}", [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])
        ->assertRedirect("/admin/users/{$user->id}");

    expect($user->fresh()->name)->toBe('New Name');
    expect($user->fresh()->email)->toBe('new@example.com');
});

it('rejects email already taken by another user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($admin)
        ->patch("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');
});

it('admin cannot update a soft-deleted user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($admin)
        ->patch("/admin/users/{$user->id}", ['name' => 'New Name', 'email' => $user->email])
        ->assertForbidden();
});

it('non-admin cannot update a user', function () {
    $requester = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($requester)
        ->patch("/admin/users/{$target->id}", ['name' => 'X', 'email' => $target->email])
        ->assertForbidden();
});
