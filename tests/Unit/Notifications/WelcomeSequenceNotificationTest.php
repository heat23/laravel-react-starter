<?php

use App\Models\User;
use App\Notifications\WelcomeSequenceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sends via database and mail channels for verified users', function () {
    $user = User::factory()->create();
    $notification = new WelcomeSequenceNotification(1);

    $channels = $notification->via($user);

    expect($channels)->toContain('database')
        ->toContain('mail');
});

it('sends via database only for unverified users', function () {
    $user = User::factory()->unverified()->create();
    $notification = new WelcomeSequenceNotification(1);

    $channels = $notification->via($user);

    expect($channels)->toContain('database')
        ->not->toContain('mail');
});

it('renders email 1 — welcome', function () {
    $user = User::factory()->create();
    $notification = new WelcomeSequenceNotification(1);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('Welcome');
});

it('renders email 2 — getting started', function () {
    $user = User::factory()->create();
    $notification = new WelcomeSequenceNotification(2);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('3 things to try first');
});

it('renders email 3 — advanced features', function () {
    $user = User::factory()->create();
    $notification = new WelcomeSequenceNotification(3);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('Unlock the full power');
});

it('includes email_number in toArray', function () {
    $user = User::factory()->create();
    $notification = new WelcomeSequenceNotification(2);

    $data = $notification->toArray($user);

    expect($data)->toHaveKey('type', 'welcome_sequence_2')
        ->toHaveKey('email_number', 2);
});
