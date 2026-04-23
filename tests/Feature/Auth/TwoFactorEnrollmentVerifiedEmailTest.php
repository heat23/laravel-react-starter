<?php

use App\Http\Controllers\Settings\TwoFactorController;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);
});

// The route group has `verified` middleware which redirects unverified users before they
// reach TwoFactorController. These tests bypass that middleware to exercise the controller's
// own hasVerifiedEmail() check — a defense-in-depth gate for code paths that bypass routing.
it('unverified user cannot enable 2FA (controller gate)', function () {
    $user = User::factory()->unverified()->create();

    $this->withoutMiddleware(EnsureEmailIsVerified::class)
        ->actingAs($user)
        ->post('/settings/security/enable')
        ->assertSessionHasErrors('email');
});

it('unverified user gets the correct validation message', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->withoutMiddleware(EnsureEmailIsVerified::class)
        ->actingAs($user)
        ->post('/settings/security/enable');

    $response->assertSessionHasErrors('email');
    expect(session('errors')->get('email'))
        ->toContain('You must verify your email before enabling two-factor authentication.');
});

it('verified user can enable 2FA', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->post('/settings/security/enable')
        ->assertRedirect();

    expect($user->twoFactorAuth()->exists())->toBeTrue();
});
