<?php

use App\Models\User;
use App\Notifications\WelcomeSequenceNotification;

it('email 2 CTA links to profile edit', function () {
    $notification = new WelcomeSequenceNotification(2);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('profile.edit'));
});

it('email 2 subject contains getting started copy', function () {
    $notification = new WelcomeSequenceNotification(2);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('3 things to try first');
});

it('email 3 CTA links to settings tokens when api_tokens enabled', function () {
    config(['features.api_tokens.enabled' => true]);

    $notification = new WelcomeSequenceNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('settings.tokens'));
});
