<?php

use App\Models\AuditLog;
use App\Models\EmailSendLog;
use App\Models\User;
use App\Notifications\OnboardingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends email 1 to users who registered within the email-1 window and have not completed onboarding', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->emailNumber === 1;
    });
});

it('sends email 2 to users who registered within the email-2 window and have not completed onboarding', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[2];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->emailNumber === 2;
    });
});

it('sends email 3 to users who registered within the email-3 window and have not completed onboarding', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[3];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->emailNumber === 3;
    });
});

it('does not send to unverified users', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    User::factory()->onboardingIncomplete()->unverified()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send to users who have already completed onboarding', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    // Explicitly mark onboarding as completed — do not rely on factory default state
    $user = User::factory()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);
    $user->setSetting('onboarding_completed', true);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNothingSentTo($user, OnboardingReminderNotification::class);
});

it('does not send duplicate emails via EmailSendLog deduplication', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    EmailSendLog::record($user->id, 'onboarding_reminder', 1);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('continues sending to remaining users when one user notification throws', function () {
    // Do NOT use Notification::fake() — we need the real pipeline so the
    // NotificationSending event fires and our listener can simulate a failure.
    // Force the array mail driver to prevent real email delivery regardless of env config.
    config(['mail.default' => 'array']);

    $schedule = config('email-sequences.onboarding')[1];
    $createdAt = now()->subDays($schedule['days'])->subHours(2);
    $verifiedAt = now()->subDays($schedule['days']);

    $failingUser = User::factory()->onboardingIncomplete()->create([
        'created_at' => $createdAt,
        'email_verified_at' => $verifiedAt,
    ]);

    $successUser = User::factory()->onboardingIncomplete()->create([
        'created_at' => $createdAt,
        'email_verified_at' => $verifiedAt,
    ]);

    // Make the first user's notification throw before the mail channel is invoked.
    // The exception propagates through $user->notify() and is caught by the command's
    // try/catch, allowing the loop to continue to the next user.
    Event::listen(NotificationSending::class, function ($event) use ($failingUser) {
        if ($event->notifiable->id === $failingUser->id) {
            throw new RuntimeException('Simulated notification channel failure');
        }
    });

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    // The command records success in EmailSendLog only after notify() returns without error.
    expect(EmailSendLog::alreadySent($successUser->id, 'onboarding_reminder', 1))->toBeTrue();
    expect(EmailSendLog::alreadySent($failingUser->id, 'onboarding_reminder', 1))->toBeFalse();
});

it('does not send email 3 to users with recent audit log activity', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[3];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'login',
        'ip' => '127.0.0.1',
        'created_at' => now()->subDay(),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('email 1 is not gated by recent activity (only email 3 is gated)', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'login',
        'ip' => '127.0.0.1',
        'created_at' => now()->subDay(),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    // Email 1 is NOT gated by activity — user should still receive it
    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->emailNumber === 1;
    });
});

it('email 2 is not gated by recent activity (only email 3 is gated)', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[2];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'login',
        'ip' => '127.0.0.1',
        'created_at' => now()->subDay(),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    // Email 2 is NOT gated by activity — user should still receive it
    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->emailNumber === 2;
    });
});

it('does not send to users who opted out of marketing emails', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    // Use the canonical setter so the cache is properly invalidated.
    // Raw DB writes bypass cache flushing and can leave a stale 'true' entry
    // that makes hasOptedOut() return false even after the opt-out is stored.
    $user->setSetting('marketing_emails', false);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('does not send email 1 to users whose account is older than the email-1 max_days window', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    // created_at is max_days + 2 hours ago — just beyond the upper boundary
    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['max_days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['max_days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('does not send email 2 to users whose account is older than the email-2 max_days window', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[2];

    // created_at is max_days + 2 hours ago — just beyond the upper boundary
    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['max_days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['max_days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('does not send email 3 to users whose account is older than the email-3 max_days window', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[3];

    // created_at is max_days + 2 hours ago — just beyond the upper boundary
    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['max_days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['max_days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, OnboardingReminderNotification::class);
});

it('reports sent count in output', function () {
    Notification::fake();

    $schedule = config('email-sequences.onboarding')[1];

    User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->expectsOutputToContain('Sent 1 onboarding reminders')
        ->assertSuccessful();
});

it('exits successfully with zero reminders when no eligible users exist', function () {
    Notification::fake();

    $this->artisan('notifications:send-onboarding')
        ->expectsOutputToContain('Sent 0 onboarding reminders')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('uses onboarding route as CTA when onboarding feature is enabled', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => true]);

    $schedule = config('email-sequences.onboarding')[1];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->ctaUrl === route('onboarding');
    });
});

it('uses dashboard route as CTA when onboarding feature is disabled', function () {
    Notification::fake();
    config(['features.onboarding.enabled' => false]);

    $schedule = config('email-sequences.onboarding')[1];

    $user = User::factory()->onboardingIncomplete()->create([
        'created_at' => now()->subDays($schedule['days'])->subHours(2),
        'email_verified_at' => now()->subDays($schedule['days']),
    ]);

    $this->artisan('notifications:send-onboarding')
        ->assertSuccessful();

    Notification::assertSentTo($user, OnboardingReminderNotification::class, function ($n) {
        return $n->ctaUrl === route('dashboard');
    });
});
