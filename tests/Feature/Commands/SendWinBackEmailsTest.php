<?php

use App\Models\EmailSendLog;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\WinBackNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('sends win-back email 1 to user canceled 4 days ago', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_test',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertSentTo($user, WinBackNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('does not send when billing is disabled', function () {
    Notification::fake();
    config(['features.billing.enabled' => false]);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send to users who have reactivated', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_reactivated',
    ]);

    // Canceled subscription that ended 4 days ago
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    // New active subscription
    createSubscription($user, ['stripe_status' => 'active']);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WinBackNotification::class);
});

it('does not send to users who opted out of marketing emails', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_optout',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    UserSetting::setValue($user->id, 'marketing_emails', false);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WinBackNotification::class);
});

it('does not send duplicate win-back emails', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_dup',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    // Use EmailSendLog to simulate having already sent win-back email 1
    EmailSendLog::record($user->id, 'win_back', 1);

    Notification::fake();

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WinBackNotification::class);
});

it('dedup is independent of notification table — EmailSendLog is the source of truth', function () {
    // MSG-023 regression anchor: win-back dedup uses EmailSendLog, not the notifications
    // table. Even after prune-read-notifications removes old notification rows, the
    // EmailSendLog record persists and prevents re-send.
    //
    // Config is set explicitly here so the window coverage is guaranteed regardless of
    // what email-sequences.win_back contains in the environment. Day 31 must fall within
    // email 3's window (days=30, max_days=33) for the candidate to be evaluated at all —
    // without this, the assertion could pass vacuously because the subscription never
    // enters the candidate set and the dedup logic is never exercised.
    config(['email-sequences.win_back' => [
        1 => ['days' => 3,  'max_days' => 5],
        2 => ['days' => 14, 'max_days' => 17],
        3 => ['days' => 30, 'max_days' => 33],
    ]]);

    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_dedup_log',
    ]);

    // Subscription ended 31 days ago — inside the email 3 window (30-33 days).
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(31),
    ]);

    // Simulate: email 3 was already sent (EmailSendLog has the record).
    // No matching row exists in the notifications table (as if it had been pruned).
    EmailSendLog::record($user->id, 'win_back', 3);

    // Baseline: exactly 1 EmailSendLog row exists before the command runs.
    $logCountBefore = EmailSendLog::count();
    expect($logCountBefore)->toBe(1);

    $this->artisan('emails:send-win-back')->assertSuccessful();

    // Must NOT resend — EmailSendLog dedup fires before any notification lookup.
    Notification::assertNotSentTo($user, WinBackNotification::class);

    // Confirm no new EmailSendLog entry was written. If this fails, the candidate was
    // evaluated but dedup did not fire — meaning dedup is broken, not just missing.
    expect(EmailSendLog::count())->toBe(1);

    // Confirm the original EmailSendLog entry is still present (not cleared by the command).
    expect(EmailSendLog::alreadySent($user->id, 'win_back', 3))->toBeTrue();
});

it('records win-back email in EmailSendLog after sending', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_log',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertSentTo($user, WinBackNotification::class);
    expect(EmailSendLog::alreadySent($user->id, 'win_back', 1))->toBeTrue();
});
