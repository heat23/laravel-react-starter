<?php

use App\Models\User;
use App\Notifications\ReEngagementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends email to users inactive for 7 days', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now()->subDays(30),
        'last_active_at' => now()->subDays(8),
    ]);

    $this->artisan('emails:send-re-engagement')
        ->expectsOutputToContain('re-engagement emails')
        ->assertSuccessful();

    Notification::assertSentTo($user, ReEngagementNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('does not send to recently active users', function () {
    Notification::fake();

    User::factory()->create([
        'email_verified_at' => now()->subDays(30),
        'last_active_at' => now()->subDays(3),
    ]);

    $this->artisan('emails:send-re-engagement')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('falls back to last_login_at when last_active_at is null', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now()->subDays(30),
        'last_active_at' => null,
        'last_login_at' => now()->subDays(8),
    ]);

    $this->artisan('emails:send-re-engagement')
        ->assertSuccessful();

    Notification::assertSentTo($user, ReEngagementNotification::class);
});

it('does not send to unverified users', function () {
    Notification::fake();

    User::factory()->unverified()->create([
        'last_active_at' => now()->subDays(8),
    ]);

    $this->artisan('emails:send-re-engagement')
        ->assertSuccessful();

    Notification::assertNothingSent();
});
