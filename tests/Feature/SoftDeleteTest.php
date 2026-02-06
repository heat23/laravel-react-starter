<?php

use App\Models\User;
use App\Models\UserSetting;

test('soft deleted user cannot log in', function () {
    $user = User::factory()->create([
        'email' => 'deleted@example.com',
        'password' => bcrypt('password'),
    ]);

    $user->delete();

    $this->post('/login', [
        'email' => 'deleted@example.com',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('soft deleted user preserves related data', function () {
    $user = User::factory()->create();

    if (config('features.user_settings.enabled', true)) {
        UserSetting::setValue($user->id, 'theme', 'dark');
    }

    $user->delete();

    $this->assertSoftDeleted($user);

    if (config('features.user_settings.enabled', true)) {
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'theme',
        ]);
    }
});

test('soft deleted user is restorable', function () {
    $user = User::factory()->create();

    $user->delete();
    $this->assertSoftDeleted($user);

    $user->restore();
    $this->assertNotSoftDeleted($user);
    $this->assertNotNull($user->fresh());
});

test('soft deleted user excluded from default queries', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $user->delete();

    $this->assertNull(User::find($userId));
    $this->assertNotNull(User::withTrashed()->find($userId));
});
