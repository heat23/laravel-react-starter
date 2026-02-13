<?php

use App\Models\User;

it('allows through when onboarding feature is disabled', function () {
    config()->set('features.onboarding.enabled', false);

    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

it('redirects to onboarding when not completed', function () {
    config()->set('features.onboarding.enabled', true);

    $user = User::factory()->onboardingIncomplete()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('onboarding'));
});

it('allows through when onboarding is completed', function () {
    config()->set('features.onboarding.enabled', true);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->setSetting('onboarding_completed', now()->toISOString());

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

it('allows unauthenticated requests through', function () {
    config()->set('features.onboarding.enabled', true);

    // Unauthenticated request to dashboard will be redirected to login by auth middleware,
    // not by onboarding middleware. The onboarding middleware passes through for guests.
    $response = $this->get(route('dashboard'));

    // Auth middleware redirects to login, not onboarding
    $response->assertRedirect(route('login'));
});
