<?php

use App\Models\User;
use App\Notifications\TrialNudgeNotification;
use Carbon\Carbon;

it('email 1 CTA links to pricing when billing enabled', function () {
    config(['features.billing.enabled' => true]);

    $notification = new TrialNudgeNotification(1, Carbon::now()->addDays(7));
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('pricing'));
});

it('email 2 CTA links to billing index when billing enabled', function () {
    config(['features.billing.enabled' => true]);

    $notification = new TrialNudgeNotification(2, Carbon::now()->addDays(3));
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('billing.index'));
});

it('email 3 CTA links to billing index when billing enabled', function () {
    config(['features.billing.enabled' => true]);

    $notification = new TrialNudgeNotification(3, Carbon::now()->subDay());
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('billing.index'));
});

it('email 2 CTA links to dashboard when billing disabled', function () {
    config(['features.billing.enabled' => false]);

    $notification = new TrialNudgeNotification(2, Carbon::now()->addDays(3));
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('dashboard'));
});

it('email 3 CTA links to dashboard when billing disabled', function () {
    config(['features.billing.enabled' => false]);

    $notification = new TrialNudgeNotification(3, Carbon::now()->subDay());
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('dashboard'));
});

it('email 3 does not reference data retention or deletion schedule', function () {
    config(['features.billing.enabled' => true]);

    $notification = new TrialNudgeNotification(3, Carbon::now()->subDay());
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    $body = collect($mailMessage->introLines)->join(' ');

    expect($body)->not->toContain('30 days');
});

it('includes correct database payload', function () {
    $trialEndsAt = Carbon::now()->addDays(7);
    $notification = new TrialNudgeNotification(2, $trialEndsAt);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data)->toBe([
        'type' => 'trial_nudge_2',
        'email_number' => 2,
        'trial_ends_at' => $trialEndsAt->toISOString(),
    ]);
});
