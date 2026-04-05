<?php

use App\Enums\AnalyticsEvent;
use App\Jobs\DispatchAnalyticsEvent;
use App\Models\EmailSendLog;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\DunningReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('dispatches lifecycle.email_sent analytics event after sending dunning reminder', function () {
    Queue::fake();
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    Subscription::create([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_'.uniqid(),
        'stripe_status' => 'past_due',
        'stripe_price' => 'price_test',
        'quantity' => 1,
        'past_due_since' => now()->subDays(4),
    ]);

    $this->artisan('notifications:send-dunning')->assertSuccessful();

    Notification::assertSentTo($user, DunningReminderNotification::class);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === AnalyticsEvent::LIFECYCLE_EMAIL_SENT->value
            && $job->userId === $user->id
            && $job->params['email_type'] === 'dunning_reminder'
            && $job->params['email_number'] === 1;
    });
});

it('does not dispatch analytics event when dunning email was already sent', function () {
    Queue::fake();
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    Subscription::create([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_'.uniqid(),
        'stripe_status' => 'past_due',
        'stripe_price' => 'price_test',
        'quantity' => 1,
        'past_due_since' => now()->subDays(4),
    ]);

    EmailSendLog::record($user->id, 'dunning_reminder', 1);

    $this->artisan('notifications:send-dunning')->assertSuccessful();

    Notification::assertNothingSent();
    Queue::assertNotPushed(DispatchAnalyticsEvent::class);
});
