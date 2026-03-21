<?php

use App\Listeners\SendWelcomeNotification;
use App\Models\User;
use App\Notifications\WelcomeSequenceNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('sends welcome sequence email 1 on user registration', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    event(new Registered($user));

    Notification::assertSentTo($user, WelcomeSequenceNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('sends only one welcome email per registration', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    event(new Registered($user));

    Notification::assertSentToTimes($user, WelcomeSequenceNotification::class, 1);
});

it('sends welcome sequence via both channels for verified users', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    event(new Registered($user));

    Notification::assertSentTo($user, WelcomeSequenceNotification::class, function ($notification, $channels) {
        return in_array('database', $channels) && in_array('mail', $channels);
    });
});

it('sends welcome sequence via database only for unverified users', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => null]);

    event(new Registered($user));

    Notification::assertSentTo($user, WelcomeSequenceNotification::class, function ($notification, $channels) {
        return in_array('database', $channels) && ! in_array('mail', $channels);
    });
});

it('email 1 subject contains welcome copy', function () {
    $notification = new WelcomeSequenceNotification(1);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('Welcome');
});

it('email 1 primary action links to dashboard', function () {
    $notification = new WelcomeSequenceNotification(1);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('dashboard'));
});

it('includes correct database payload for email 1', function () {
    $notification = new WelcomeSequenceNotification(1);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data)->toBe([
        'type' => 'welcome_sequence_1',
        'email_number' => 1,
    ]);
});

it('registers SendWelcomeNotification listener for Registered event', function () {
    Event::fake();

    Event::assertListening(Registered::class, SendWelcomeNotification::class);
});
