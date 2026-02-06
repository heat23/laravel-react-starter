<?php

use App\Models\User;

test('charts page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/charts')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard/Charts'));
});

test('charts page requires authentication', function () {
    $this->get('/dashboard/charts')
        ->assertRedirect('/login');
});
