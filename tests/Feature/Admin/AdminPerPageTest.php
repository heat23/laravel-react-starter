<?php

use App\Models\User;

it('admin users list respects per_page param', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(30)->create();

    $response = $this->actingAs($admin)
        ->get('/admin/users?per_page=10');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('users.per_page', 10)
    );
});

it('rejects invalid per_page values', function () {
    $admin = User::factory()->admin()->create();

    // Invalid per_page fails validation
    $this->actingAs($admin)
        ->get('/admin/users?per_page=999')
        ->assertSessionHasErrors('per_page');
});
