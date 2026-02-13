<?php

use App\Models\User;

it('requires authentication', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('renders dashboard for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});
