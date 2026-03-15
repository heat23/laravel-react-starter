<?php

use App\Listeners\SendWelcomeNotification;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('sends welcome notification on user registration', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    event(new Registered($user));

    Notification::assertSentTo($user, WelcomeNotification::class);
});

it('sends welcome notification via both channels for verified users', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    event(new Registered($user));

    Notification::assertSentTo($user, WelcomeNotification::class, function ($notification, $channels) {
        return in_array('database', $channels) && in_array('mail', $channels);
    });
});

it('sends welcome notification via database only for unverified users', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => null]);

    event(new Registered($user));

    Notification::assertSentTo($user, WelcomeNotification::class, function ($notification, $channels) {
        return in_array('database', $channels) && ! in_array('mail', $channels);
    });
});

it('includes dashboard link in welcome notification', function () {
    $notification = new WelcomeNotification;
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Welcome to '.config('app.name'));
    expect($mailMessage->actionUrl)->toBe(route('dashboard'));
});

it('includes correct database payload', function () {
    $notification = new WelcomeNotification;
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data)->toBe([
        'type' => 'welcome',
        'actionUrl' => route('dashboard'),
    ]);
});

it('registers SendWelcomeNotification listener for Registered event', function () {
    Event::fake();

    Event::assertListening(Registered::class, SendWelcomeNotification::class);
});
