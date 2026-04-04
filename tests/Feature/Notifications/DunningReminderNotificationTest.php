<?php

use App\Models\EmailSendLog;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\DunningReminderNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('sends dunning email 1 with gentle reminder', function () {
    $notification = new DunningReminderNotification(emailNumber: 1);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Your payment method needs updating');
    expect($mailMessage->actionUrl)->toBe(route('billing.index'));
});

it('sends dunning email 2 with urgency and plan name', function () {
    $notification = new DunningReminderNotification(emailNumber: 2, planName: 'Pro');
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Action needed — your subscription will be paused');
});

it('sends dunning email 3 as final notice', function () {
    $notification = new DunningReminderNotification(emailNumber: 3, planName: 'Pro');
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Final notice — subscription will cancel tomorrow');
});

it('uses database and mail channels for verified users', function () {
    $notification = new DunningReminderNotification(emailNumber: 1);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $channels = $notification->via($user);

    expect($channels)->toContain('database')
        ->and($channels)->toContain('mail');
});

it('uses database only for unverified users', function () {
    $notification = new DunningReminderNotification(emailNumber: 1);
    $user = User::factory()->create(['email_verified_at' => null]);

    $channels = $notification->via($user);

    expect($channels)->toContain('database')
        ->and($channels)->not->toContain('mail');
});

it('includes plan name and email number in database payload', function () {
    $notification = new DunningReminderNotification(emailNumber: 2, planName: 'Team');
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data['type'])->toBe('dunning_reminder_2')
        ->and($data['email_number'])->toBe(2)
        ->and($data['plan_name'])->toBe('Team')
        ->and($data['actionUrl'])->toBe(route('billing.index'));
});

it('sends dunning reminders via the artisan command', function () {
    Notification::fake();

    $user = User::factory()->create([
        'stripe_id' => 'cus_dunning_test',
        'email_verified_at' => now(),
    ]);

    // Create a past_due subscription that went past_due 4 days ago
    createSubscription($user, [
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
    ]);

    $this->artisan('notifications:send-dunning')
        ->assertSuccessful();

    Notification::assertSentTo($user, DunningReminderNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('does not send dunning reminders when billing is disabled', function () {
    Notification::fake();
    config(['features.billing.enabled' => false]);

    $this->artisan('notifications:send-dunning')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('dunning is transactional and must be sent even when user opted out of marketing emails', function () {
    Notification::fake();

    $user = User::factory()->create([
        'stripe_id' => 'cus_dunning_optout_test',
        'email_verified_at' => now(),
    ]);

    // Opt the user out of marketing emails — dunning is transactional and must ignore this
    UserSetting::setValue($user->id, 'marketing_emails', false);

    createSubscription($user, [
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
    ]);

    $this->artisan('notifications:send-dunning')
        ->assertSuccessful();

    Notification::assertSentTo($user, DunningReminderNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('does not send duplicate dunning reminders', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_dunning_dup',
        'email_verified_at' => now(),
    ]);

    createSubscription($user, [
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
    ]);

    // Insert an EmailSendLog record to simulate having already sent email #1
    EmailSendLog::record($user->id, 'dunning_reminder', 1);

    Notification::fake();

    $this->artisan('notifications:send-dunning')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, DunningReminderNotification::class);
});
