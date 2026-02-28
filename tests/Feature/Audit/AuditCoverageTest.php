<?php

use App\Jobs\PersistAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

it('logs password change event', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    Queue::assertPushed(PersistAuditLog::class, function ($job) {
        $reflect = new ReflectionClass($job);

        return $reflect->getProperty('event')->getValue($job) === 'auth.password_changed';
    });
});

it('logs profile update event', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile', [
        'name' => 'Updated Name',
        'email' => $user->email,
    ]);

    Queue::assertPushed(PersistAuditLog::class, function ($job) {
        $reflect = new ReflectionClass($job);

        return $reflect->getProperty('event')->getValue($job) === 'profile.updated';
    });
});

it('logs API token creation event', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/tokens', [
        'name' => 'Test Token',
    ]);

    Queue::assertPushed(PersistAuditLog::class, function ($job) {
        $reflect = new ReflectionClass($job);

        return $reflect->getProperty('event')->getValue($job) === 'api_token.created';
    });
});

it('logs API token deletion event', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    $this->actingAs($user)->deleteJson("/api/tokens/{$token->accessToken->id}");

    Queue::assertPushed(PersistAuditLog::class, function ($job) {
        $reflect = new ReflectionClass($job);

        return $reflect->getProperty('event')->getValue($job) === 'api_token.deleted';
    });
});

it('logs account deletion event', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    Queue::assertPushed(PersistAuditLog::class, function ($job) {
        $reflect = new ReflectionClass($job);

        return $reflect->getProperty('event')->getValue($job) === 'account.deleted';
    });
});

it('logs login event', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    Queue::assertPushed(PersistAuditLog::class, function ($job) {
        $reflect = new ReflectionClass($job);

        return $reflect->getProperty('event')->getValue($job) === 'auth.login';
    });
});

it('logs registration event', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'password' => 'password-123',
        'password_confirmation' => 'password-123',
    ]);

    Queue::assertPushed(PersistAuditLog::class, function ($job) {
        $reflect = new ReflectionClass($job);

        return $reflect->getProperty('event')->getValue($job) === 'auth.register';
    });
});
