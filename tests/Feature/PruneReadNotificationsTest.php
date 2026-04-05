<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('prune deletes read notifications older than default retention', function () {
    $user = User::factory()->create();

    // Old read notification — beyond 60-day default
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(61),
        'created_at' => now()->subDays(65),
    ]);

    // Recent read notification — within retention window
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 2]),
        'read_at' => now()->subDays(14),
        'created_at' => now()->subDays(14),
    ]);

    $this->artisan('prune-read-notifications')
        ->expectsOutputToContain('Pruned 1 read notifications older than 60 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(1);
});

test('prune preserves unread notifications regardless of age', function () {
    $user = User::factory()->create();

    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => null,
        'created_at' => now()->subDays(90),
    ]);

    $this->artisan('prune-read-notifications')
        ->expectsOutputToContain('Pruned 0 read notifications older than 60 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(1);
});

test('prune respects custom days option', function () {
    $user = User::factory()->create();

    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(35),
        'created_at' => now()->subDays(35),
    ]);

    // With --days=30 the 35-day-old record should be deleted
    $this->artisan('prune-read-notifications', ['--days' => 30])
        ->expectsOutputToContain('Pruned 1 read notifications older than 30 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(0);
});

test('default retention of 60 days preserves win-back notifications during full sequence', function () {
    // Win-back sequence: email 1 at day 3, email 2 at day 14, email 3 at day 30-33.
    // With a 60-day default, emails 1 and 2 remain in notifications table throughout the
    // sequence, preserving in-app notification history for the user.
    $user = User::factory()->create();

    // Email 1: sent at day 3, read at day 3 (33 days ago relative to now at day 33+3)
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(33),
        'created_at' => now()->subDays(33),
    ]);

    // Email 2: sent at day 14, read at day 14 (19 days ago)
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 2]),
        'read_at' => now()->subDays(19),
        'created_at' => now()->subDays(19),
    ]);

    // Both notifications are < 60 days old — neither should be pruned
    $this->artisan('prune-read-notifications')
        ->expectsOutputToContain('Pruned 0 read notifications older than 60 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(2);
});
