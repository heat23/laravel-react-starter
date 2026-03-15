<?php

use App\Models\User;
use App\Notifications\ReEngagementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders email 1 — gentle check-in', function () {
    $user = User::factory()->create();
    $notification = new ReEngagementNotification(1);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain("haven't seen you");
});

it('renders email 2 — feedback request', function () {
    $user = User::factory()->create();
    $notification = new ReEngagementNotification(2);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('Quick question');
});

it('renders email 3 — account status', function () {
    $user = User::factory()->create();
    $notification = new ReEngagementNotification(3);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('still active');
});

it('includes email_number in toArray', function () {
    $user = User::factory()->create();
    $notification = new ReEngagementNotification(2);

    $data = $notification->toArray($user);

    expect($data)->toHaveKey('type', 're_engagement_2')
        ->toHaveKey('email_number', 2);
});
