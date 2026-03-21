<?php

use App\Models\User;
use App\Notifications\OnboardingReminderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('sends onboarding email 1 with correct subject', function () {
    $notification = new OnboardingReminderNotification(emailNumber: 1);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('3 things to set up in your first 5 minutes');
    expect($mailMessage->actionUrl)->toBe(route('dashboard'));
});

it('sends onboarding email 2 with feature highlight', function () {
    $notification = new OnboardingReminderNotification(emailNumber: 2);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('Did you know');
    expect($mailMessage->actionUrl)->toBe(route('dashboard'));
});

it('sends onboarding email 3 with help offer', function () {
    $notification = new OnboardingReminderNotification(emailNumber: 3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Quick question — is everything working?');
    expect($mailMessage->actionUrl)->toBe(route('dashboard'));
});

it('uses mail channel only for onboarding reminders', function () {
    $notification = new OnboardingReminderNotification(emailNumber: 1);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $channels = $notification->via($user);

    expect($channels)->toBe(['mail']);
});

it('includes email number in database payload', function () {
    $notification = new OnboardingReminderNotification(emailNumber: 2);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data)->toBe([
        'type' => 'onboarding_reminder_2',
        'email_number' => 2,
    ]);
});

it('sends onboarding reminders via the artisan command', function () {
    config(['features.onboarding.enabled' => true]);

    // User registered 36 hours ago, onboarding NOT completed
    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subHours(36),
        'created_at' => now()->subHours(36),
    ]);

    Notification::fake();

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('sends onboarding reminders with dashboard CTA when feature is disabled', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => false]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subHours(25),
        'created_at' => now()->subHours(25),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($notification) {
        return $notification->ctaUrl === route('dashboard');
    });
});

it('does not send duplicate onboarding reminders', function () {
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->create([
        'email_verified_at' => now()->subHours(25),
        'created_at' => now()->subHours(25),
    ]);

    // Insert a notification record directly to simulate having already received this
    $user->notifications()->create([
        'id' => Str::uuid(),
        'type' => OnboardingReminderNotification::class,
        'data' => ['type' => 'onboarding_reminder_1', 'email_number' => 1],
    ]);

    Notification::fake();

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('skips users who completed onboarding', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);
    config(['features.user_settings.enabled' => true]);

    $user = User::factory()->create([
        'email_verified_at' => now()->subHours(25),
        'created_at' => now()->subHours(25),
    ]);

    // Mark onboarding as completed
    $user->setSetting('onboarding_completed', true);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('skips unverified users', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    User::factory()->create([
        'email_verified_at' => null,
        'created_at' => now()->subHours(25),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNothingSent();
});
