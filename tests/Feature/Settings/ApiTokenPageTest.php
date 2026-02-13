<?php

use App\Models\User;

it('requires authentication', function () {
    $this->get('/settings/tokens')->assertRedirect('/login');
});

it('renders api tokens page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/tokens')->assertOk();
});
