<?php

use App\Models\User;
use App\Notifications\OnboardingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sends via database and mail channels for verified users', function () {
    $user = User::factory()->create();
    $notification = new OnboardingReminderNotification(1);

    $channels = $notification->via($user);

    expect($channels)->toContain('database')
        ->toContain('mail');
});

it('sends via database only for unverified users', function () {
    $user = User::factory()->unverified()->create();
    $notification = new OnboardingReminderNotification(1);

    $channels = $notification->via($user);

    expect($channels)->toContain('database')
        ->not->toContain('mail');
});

it('renders email 1 — getting started', function () {
    $user = User::factory()->create();
    $notification = new OnboardingReminderNotification(1);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('3 things to set up');
});

it('renders email 2 — feature highlight', function () {
    $user = User::factory()->create();
    $notification = new OnboardingReminderNotification(2);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('Did you know');
});

it('renders email 3 — need help', function () {
    $user = User::factory()->create();
    $notification = new OnboardingReminderNotification(3);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('Quick question');
});

it('uses custom CTA URL when provided', function () {
    $user = User::factory()->create();
    $notification = new OnboardingReminderNotification(1, 'https://example.com/cta');

    $mail = $notification->toMail($user);

    expect($mail->actionUrl)->toBe('https://example.com/cta');
    // CTA URL must only appear as the action URL, not duplicated into body lines
    expect($mail->introLines)->not->toContain('https://example.com/cta');
});

it('falls back to getting-started email for unknown email numbers', function () {
    $user = User::factory()->create();
    $notification = new OnboardingReminderNotification(99);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('3 things to set up');
});

it('returns correct toArray shape', function () {
    $notification = new OnboardingReminderNotification(2);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data)->toBe([
        'type' => 'onboarding_reminder_2',
        'email_number' => 2,
    ]);
});
