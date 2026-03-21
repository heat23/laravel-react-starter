<?php

use App\Models\User;
use App\Notifications\TrialNudgeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders email 1 — halfway', function () {
    $user = User::factory()->create();
    $notification = new TrialNudgeNotification(1);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('days left');
});

it('renders email 2 — urgency', function () {
    $user = User::factory()->create();
    $notification = new TrialNudgeNotification(2, now()->addDays(5));

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('days left');
});

it('renders email 3 — expired', function () {
    $user = User::factory()->create();
    $notification = new TrialNudgeNotification(3);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('ended');
});

it('includes email_number in toArray', function () {
    $user = User::factory()->create();
    $notification = new TrialNudgeNotification(1);

    $data = $notification->toArray($user);

    expect($data)->toHaveKey('type', 'trial_nudge_1')
        ->toHaveKey('email_number', 1);
});
