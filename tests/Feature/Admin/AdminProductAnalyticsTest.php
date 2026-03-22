<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('admin can view product analytics page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/ProductAnalytics'));
});

it('requires admin to view product analytics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertForbidden();
});

it('unauthenticated user cannot view product analytics', function () {
    $this->get('/admin/analytics')
        ->assertRedirect('/login');
});
