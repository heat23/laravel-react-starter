<?php

use App\Models\User;

it('admin can view the create user form', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/users/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('App/Admin/Users/Create'));
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

it('super admin can create a user with admin role', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->post('/admin/users', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'Password1!',
            'is_admin' => true,
        ])
        ->assertRedirect('/admin/users');

    expect(User::where('email', 'newadmin@example.com')->first()->is_admin)->toBeTrue();
});

it('regular admin cannot grant admin role when creating a user', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'Password1!',
            'is_admin' => true,
        ])
        ->assertRedirect('/admin/users');

    expect(User::where('email', 'newadmin@example.com')->first()->is_admin)->toBeFalse();
});

it('auto-verifies email when creating a user with admin role', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->post('/admin/users', [
            'name' => 'New Admin',
            'email' => 'verifiedadmin@example.com',
            'password' => 'Password1!',
            'is_admin' => true,
        ])
        ->assertRedirect('/admin/users');

    $created = User::where('email', 'verifiedadmin@example.com')->first();
    expect($created->email_verified_at)->not->toBeNull();
});

it('does not auto-verify email when creating a non-admin user', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => 'Password1!',
        ])
        ->assertRedirect('/admin/users');

    $created = User::where('email', 'regular@example.com')->first();
    expect($created->email_verified_at)->toBeNull();
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
