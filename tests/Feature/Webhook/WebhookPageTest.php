<?php

use App\Models\User;

it('returns 404 when webhooks feature is disabled', function () {
    config(['features.webhooks.enabled' => false]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/webhooks');

    $response->assertNotFound();
});

it('renders webhooks page when feature is enabled', function () {
    config(['features.webhooks.enabled' => true]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/webhooks');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Settings/Webhooks')
        ->has('available_events')
    );
});

it('requires authentication', function () {
    config(['features.webhooks.enabled' => true]);

    $response = $this->get('/settings/webhooks');

    $response->assertRedirect(route('login'));
});
