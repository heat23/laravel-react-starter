<?php

use App\Models\User;
use App\Models\UserSetting;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    config(['features.two_factor.enabled' => true]);

    if (! \Schema::hasTable('two_factor_authentications')) {
        \Schema::create('two_factor_authentications', function ($table) {
            $table->id();
            $table->morphs('authenticatable');
            $table->text('shared_secret');
            $table->timestampTz('enabled_at')->nullable();
            $table->string('label');
            $table->unsignedTinyInteger('digits')->default(6);
            $table->unsignedTinyInteger('seconds')->default(30);
            $table->unsignedTinyInteger('window')->default(1);
            $table->string('algorithm', 16)->default('sha1');
            $table->json('recovery_codes')->nullable();
            $table->timestampTz('recovery_codes_generated_at')->nullable();
            $table->timestamps();
        });
    }
});

it('hard deletes user and all personal data on account deletion', function () {
    $user = User::factory()->create();

    // Create related data
    $user->createToken('test-token');
    UserSetting::setValue($user->id, 'theme', 'dark');

    if (\Schema::hasTable('webhook_endpoints')) {
        WebhookEndpoint::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/webhook',
            'secret' => 'test-secret',
            'events' => ['user.created'],
            'is_active' => true,
        ]);
    }

    $userId = $user->id;

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertGuest();

    // User is permanently gone (not just soft-deleted)
    expect(User::withTrashed()->find($userId))->toBeNull();

    // Related data cleaned up
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $userId,
        'tokenable_type' => User::class,
    ]);
    $this->assertDatabaseMissing('user_settings', ['user_id' => $userId]);

    if (\Schema::hasTable('webhook_endpoints')) {
        $this->assertDatabaseMissing('webhook_endpoints', ['user_id' => $userId]);
    }
});

it('preserves audit log entries with null user_id after hard delete', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    // Insert audit log directly to avoid queue faking
    \App\Models\AuditLog::create([
        'event' => 'test.event',
        'user_id' => $userId,
        'ip' => '127.0.0.1',
        'user_agent' => 'test',
        'metadata' => ['test' => true],
    ]);

    $this->assertDatabaseHas('audit_logs', ['user_id' => $userId]);

    $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    // Audit log entry preserved but user_id nulled (FK nullOnDelete)
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => null,
        'event' => 'test.event',
    ]);
});

it('removes 2FA data on hard delete', function () {
    $user = User::factory()->create();
    $user->createTwoFactorAuth();

    $userId = $user->id;

    $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    expect(User::withTrashed()->find($userId))->toBeNull();
    $this->assertDatabaseMissing('two_factor_authentications', [
        'authenticatable_id' => $userId,
        'authenticatable_type' => User::class,
    ]);
});
