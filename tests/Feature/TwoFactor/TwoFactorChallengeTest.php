<?php

use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);
    Queue::fake();
});

it('redirects to 2FA challenge when user has 2FA enabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.challenge'));
    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);
});

it('skips 2FA challenge when user has no 2FA', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

it('skips 2FA challenge when feature disabled', function () {
    config(['features.two_factor.enabled' => false]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

it('renders challenge page when login.id in session', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->withSession(['login.id' => $user->id])
        ->get('/two-factor-challenge');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/TwoFactorChallenge')
    );
});

it('redirects to login when no login.id in session', function () {
    $response = $this->get('/two-factor-challenge');

    $response->assertRedirect(route('login'));
});

it('completes login with valid TOTP code', function () {
    $user = User::factory()->withTwoFactor()->create();

    $code = $user->twoFactorAuth->makeCode();

    $response = $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post('/two-factor-challenge', ['code' => $code]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid TOTP code', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->withSession(['login.id' => $user->id])
        ->post('/two-factor-challenge', ['code' => '000000']);

    $response->assertRedirect();
    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

it('redirects to login when session expired during challenge', function () {
    $response = $this->post('/two-factor-challenge', ['code' => '123456']);

    $response->assertRedirect(route('login'));
});

it('validates that code or recovery_code is required', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->withSession(['login.id' => $user->id])
        ->post('/two-factor-challenge', []);

    $response->assertSessionHasErrors('code');
});
