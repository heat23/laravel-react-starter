<?php

use App\Models\User;

beforeEach(function () {
    config()->set('features.onboarding.enabled', true);
});

it('shows onboarding page for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/onboarding');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Onboarding'));
});

it('redirects unauthenticated user to login', function () {
    $response = $this->get('/onboarding');

    $response->assertRedirect(route('login'));
});

it('redirects to onboarding from dashboard when not completed', function () {
    $user = User::factory()->onboardingIncomplete()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('onboarding'));
});

it('does not redirect when onboarding is completed', function () {
    $user = User::factory()->create();
    $user->setSetting('onboarding_completed', now()->toISOString());

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

it('does not redirect when feature is disabled', function () {
    config()->set('features.onboarding.enabled', false);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

it('does not redirect loop on onboarding page itself', function () {
    $user = User::factory()->create();

    // Even without onboarding_completed, visiting /onboarding should render (not redirect)
    $response = $this->actingAs($user)->get('/onboarding');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Onboarding'));
});
