<?php

use App\Models\User;

it('admin can view the create user form', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/users/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Users/Create'));
});

it('admin can create a new user', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password1!',
        ])
        ->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('admin can create a user with admin role', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'Password1!',
            'is_admin' => true,
        ])
        ->assertRedirect('/admin/users');

    expect(User::where('email', 'newadmin@example.com')->first()->is_admin)->toBeTrue();
});

it('rejects creation with duplicate email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Test',
            'email' => 'existing@example.com',
            'password' => 'Password1!',
        ])
        ->assertSessionHasErrors('email');
});

it('non-admin cannot access create user route', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/admin/users/create')->assertForbidden();
});
