<?php

use App\Models\User;
use App\Notifications\ReEngagementNotification;

it('email 3 contains safe data message', function () {
    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    $body = collect($mailMessage->introLines)->join(' ');

    expect($body)->toContain('Your data is safe');
});

it('email 3 does not reference account deletion', function () {
    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    $body = collect($mailMessage->introLines)->join(' ');

    expect($body)->not->toContain('deleted')
        ->and($body)->not->toContain('deletion')
        ->and($body)->not->toContain('removed');
});

it('email 3 subject mentions what changed', function () {
    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('changed');
});

it('email 1 CTA links to dashboard for free users', function () {
    $notification = new ReEngagementNotification(1, isPaidUser: false);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toContain(route('dashboard'));
});

it('email 1 CTA links to billing index for paid users', function () {
    $notification = new ReEngagementNotification(1, isPaidUser: true);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('billing.index'));
});

it('email 2 CTA links to billing portal for paid users', function () {
    $notification = new ReEngagementNotification(2, isPaidUser: true);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('billing.portal'));
});

it('email 3 uses generic fallback changelog text when none provided', function () {
    config(['app.changelog_item' => null]);

    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    $body = collect($mailMessage->introLines)->join(' ');

    expect($body)
        ->not->toContain('smarter activity tracking')
        ->toContain('recent improvements');
});

it('includes correct database payload', function () {
    $notification = new ReEngagementNotification(3, isPaidUser: true);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data)->toBe([
        'type' => 're_engagement_3',
        'email_number' => 3,
        'is_paid' => true,
    ]);
});
