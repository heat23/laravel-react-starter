<?php

use App\Models\User;
use App\Notifications\WelcomeSequenceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('does not send email 1 — that is the listener\'s responsibility', function () {
    Notification::fake();

    User::factory()->create([
        'created_at' => now()->subHours(2),
        'email_verified_at' => now()->subHours(1),
    ]);

    $this->artisan('emails:send-welcome-sequence')->assertSuccessful();

    Notification::assertNothingSentTo(User::first(), WelcomeSequenceNotification::class);
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

it('sends welcome email 3 to 3-day-old users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'created_at' => now()->subDays(3)->subHours(2),
        'email_verified_at' => now()->subDays(3),
    ]);

    $this->artisan('emails:send-welcome-sequence')
        ->assertSuccessful();

    Notification::assertSentTo($user, WelcomeSequenceNotification::class, function ($notification) {
        return $notification->emailNumber === 3;
    });
});

it('does not send to unverified users', function () {
    Notification::fake();

    User::factory()->unverified()->create([
        'created_at' => now()->subDays(1)->subHours(2),
    ]);

    $this->artisan('emails:send-welcome-sequence')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send duplicate emails', function () {
    $user = User::factory()->create([
        'created_at' => now()->subDays(1)->subHours(2),
        'email_verified_at' => now()->subDays(1),
    ]);

    // Insert a real notification record in the DB for dedup check
    $user->notifications()->create([
        'id' => Str::uuid(),
        'type' => WelcomeSequenceNotification::class,
        'data' => ['type' => 'welcome_sequence_2', 'email_number' => 2],
        'read_at' => null,
    ]);

    Notification::fake();

    $this->artisan('emails:send-welcome-sequence')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WelcomeSequenceNotification::class);
});
