<?php

use App\Models\User;
use App\Notifications\WelcomeSequenceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends welcome email 1 to newly verified users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'created_at' => now()->subHours(2),
        'email_verified_at' => now()->subHours(1),
    ]);

    $this->artisan('emails:send-welcome-sequence')
        ->expectsOutputToContain('welcome sequence emails')
        ->assertSuccessful();

    Notification::assertSentTo($user, WelcomeSequenceNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('sends welcome email 2 to day-old users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'created_at' => now()->subDays(1)->subHours(2),
        'email_verified_at' => now()->subDays(1),
    ]);

    $this->artisan('emails:send-welcome-sequence')
        ->assertSuccessful();

    Notification::assertSentTo($user, WelcomeSequenceNotification::class, function ($notification) {
        return $notification->emailNumber === 2;
    });
});

it('does not send to unverified users', function () {
    Notification::fake();

    User::factory()->unverified()->create([
        'created_at' => now()->subHours(2),
    ]);

    $this->artisan('emails:send-welcome-sequence')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send duplicate emails', function () {
    Notification::fake();

    $user = User::factory()->create([
        'created_at' => now()->subHours(2),
        'email_verified_at' => now()->subHours(1),
    ]);

    // Simulate already-sent notification
    $user->notify(new WelcomeSequenceNotification(1));

    // Reset fake after seeding
    Notification::fake();

    $this->artisan('emails:send-welcome-sequence')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WelcomeSequenceNotification::class);
});
