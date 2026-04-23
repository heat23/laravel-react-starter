<?php

use App\Models\User;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);
});

it('expired 2FA session redirects to login with error', function () {
    $expiredAt = now()->subMinutes(1)->getTimestamp();

    $this->withSession([
        'login.id' => 999,
        'login.remember' => false,
        'login.expires_at' => $expiredAt,
    ])->get('/two-factor-challenge')
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    expect(session('login.id'))->toBeNull();
    expect(session('login.expires_at'))->toBeNull();
});

it('2FA session with missing expires_at redirects to login', function () {
    $this->withSession([
        'login.id' => 999,
        'login.remember' => false,
    ])->get('/two-factor-challenge')
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');
});

it('valid 2FA session within TTL renders the challenge page', function () {
    $user = User::factory()->create();
    $user->createTwoFactorAuth();

    $validAt = now()->addMinutes(14)->getTimestamp();

    $this->withSession([
        'login.id' => $user->id,
        'login.remember' => false,
        'login.expires_at' => $validAt,
    ])->get('/two-factor-challenge')
        ->assertOk();
});

it('cancel route clears session keys and redirects to login', function () {
    $this->withSession([
        'login.id' => 999,
        'login.remember' => false,
        'login.expires_at' => now()->addMinutes(10)->getTimestamp(),
    ])->post('/two-factor-cancel')
        ->assertRedirect('/login');

    expect(session('login.id'))->toBeNull();
    expect(session('login.remember'))->toBeNull();
    expect(session('login.expires_at'))->toBeNull();
});

it('AuthenticatedSessionController sets login.expires_at when 2FA is required', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->createTwoFactorAuth();
    $user->confirmTwoFactorAuth($user->twoFactorAuth->makeCode());

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/two-factor-challenge');

    expect(session('login.expires_at'))->not->toBeNull()
        ->and(session('login.expires_at'))->toBeGreaterThan(now()->getTimestamp());
});
