<?php

use App\Models\User;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);
});

it('returns false for hasTwoFactorEnabled when not set up', function () {
    $user = User::factory()->create();

    expect($user->hasTwoFactorEnabled())->toBeFalse();
});

it('returns true for hasTwoFactorEnabled when confirmed', function () {
    $user = User::factory()->withTwoFactor()->create();

    expect($user->hasTwoFactorEnabled())->toBeTrue();
});

it('returns false for hasTwoFactorEnabled after disabling', function () {
    $user = User::factory()->withTwoFactor()->create();

    expect($user->hasTwoFactorEnabled())->toBeTrue();

    $user->disableTwoFactorAuth();

    expect($user->hasTwoFactorEnabled())->toBeFalse();
});
