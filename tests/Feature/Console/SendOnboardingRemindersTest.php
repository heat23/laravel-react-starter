<?php

use App\Models\AuditLog;
use App\Models\EmailSendLog;
use App\Models\User;
use App\Notifications\OnboardingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('skips users who opted out of marketing emails', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subHours(36),
        'created_at' => now()->subHours(36),
    ]);
    $user->setSetting('marketing_emails', false);

    $this->artisan('notifications:send-onboarding')->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('sends email 2 to users who signed up 3-5 days ago', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subDays(4),
        'created_at' => now()->subDays(4),
    ]);

    $this->artisan('notifications:send-onboarding')->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->emailNumber === 2;
    });
});

it('sends email 3 to users who signed up 7-10 days ago', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subDays(8),
        'created_at' => now()->subDays(8),
    ]);

    $this->artisan('notifications:send-onboarding')->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->emailNumber === 3;
    });
});

it('skips email 3 for users with recent audit log activity', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subDays(8),
        'created_at' => now()->subDays(8),
    ]);

    // Simulate recent activity within 3 days
    AuditLog::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDays(1),
    ]);

    $this->artisan('notifications:send-onboarding')->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('records sent email in EmailSendLog', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subHours(36),
        'created_at' => now()->subHours(36),
    ]);

    $this->artisan('notifications:send-onboarding')->assertSuccessful();

    expect(EmailSendLog::alreadySent($user->id, 'onboarding_reminder', 1))->toBeTrue();
});

it('skips email 1 when welcome sequence email 2 was already sent', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subHours(36),
        'created_at' => now()->subHours(36),
    ]);

    EmailSendLog::record($user->id, 'welcome_sequence', 2);

    $this->artisan('notifications:send-onboarding')->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('does not re-send when EmailSendLog already has a record', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $user = User::factory()->onboardingIncomplete()->create([
        'email_verified_at' => now()->subHours(36),
        'created_at' => now()->subHours(36),
    ]);

    EmailSendLog::record($user->id, 'onboarding_reminder', 1);

    $this->artisan('notifications:send-onboarding')->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});
