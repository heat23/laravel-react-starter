<?php

use App\Enums\AuditEvent;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->service = new AuditService;
});

function expectLogChannel(): void
{
    Log::shouldReceive('channel')
        ->with('single')
        ->andReturnSelf();
}

// ============================================
// logLogin() tests
// ============================================

test('log login captures user id', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'auth.login'
                && $context['user_id'] === 1;
        });

    $user = User::factory()->create(['id' => 1]);
    $this->service->logLogin($user);
});

test('log login does not include email in metadata', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return ! array_key_exists('email', $context['metadata']);
        });

    $user = User::factory()->create(['email' => 'test@example.com']);
    $this->service->logLogin($user);
});

test('log login captures event name', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['event'] === 'auth.login';
        });

    $user = User::factory()->create();
    $this->service->logLogin($user);
});

test('log login captures ip address', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return isset($context['ip']);
        });

    $user = User::factory()->create();
    $this->service->logLogin($user);
});

test('log login captures user agent', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return array_key_exists('user_agent', $context);
        });

    $user = User::factory()->create();
    $this->service->logLogin($user);
});

test('log login captures timestamp', function () {
    Carbon::setTestNow('2024-01-15 12:00:00');

    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return isset($context['timestamp'])
                && str_contains($context['timestamp'], '2024-01-15');
        });

    $user = User::factory()->create();
    $this->service->logLogin($user);

    Carbon::setTestNow();
});

test('log login uses auth user when not provided', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === 1;
        });

    $user = User::factory()->create(['id' => 1, 'email' => 'auth@example.com']);
    Auth::login($user);

    $this->service->logLogin();
});

test('log login handles null user', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === null
                && empty($context['metadata']);
        });

    $this->service->logLogin(null);
});

test('log login uses single channel', function () {
    Log::shouldReceive('channel')
        ->with('single')
        ->andReturnSelf();

    Log::shouldReceive('info')
        ->once();

    $user = User::factory()->create();
    $this->service->logLogin($user);
});

// ============================================
// logLogout() tests
// ============================================

test('log logout captures correct event name', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'auth.logout'
                && $context['event'] === 'auth.logout';
        });

    $user = User::factory()->create();
    $this->service->logLogout($user);
});

test('log logout captures user id', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === 123;
        });

    $user = User::factory()->create(['id' => 123]);
    $this->service->logLogout($user);
});

test('log logout does not include email in metadata', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return ! array_key_exists('email', $context['metadata']);
        });

    $user = User::factory()->create(['email' => 'logout@example.com']);
    $this->service->logLogout($user);
});

test('log logout uses auth user when not provided', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === 1;
        });

    $user = User::factory()->create(['id' => 1]);
    Auth::login($user);

    $this->service->logLogout();
});

test('log logout captures ip', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return array_key_exists('ip', $context);
        });

    $user = User::factory()->create();
    $this->service->logLogout($user);
});

test('log logout captures timestamp', function () {
    Carbon::setTestNow('2024-06-01 08:30:00');

    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($context['timestamp'], '2024-06-01');
        });

    $user = User::factory()->create();
    $this->service->logLogout($user);

    Carbon::setTestNow();
});

// ============================================
// logRegistration() tests
// ============================================

test('log registration captures correct event name', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'auth.register'
                && $context['event'] === 'auth.register';
        });

    $user = User::factory()->create();
    $this->service->logRegistration($user);
});

test('log registration captures user id', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === 456;
        });

    $user = User::factory()->create(['id' => 456]);
    $this->service->logRegistration($user);
});

test('log registration does not include email in metadata', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return ! array_key_exists('email', $context['metadata']);
        });

    $user = User::factory()->create(['email' => 'newuser@example.com']);
    $this->service->logRegistration($user);
});

test('log registration captures signup source in metadata', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['metadata']['signup_source'] === 'google';
        });

    $user = User::factory()->create(['signup_source' => 'google']);
    $this->service->logRegistration($user);
});

test('log registration defaults signup source to direct', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['metadata']['signup_source'] === 'direct';
        });

    $user = User::factory()->create(['signup_source' => null]);
    $this->service->logRegistration($user);
});

test('log registration captures ip', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return array_key_exists('ip', $context);
        });

    $user = User::factory()->create();
    $this->service->logRegistration($user);
});

test('log registration captures timestamp', function () {
    Carbon::setTestNow('2024-12-25 00:00:00');

    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($context['timestamp'], '2024-12-25');
        });

    $user = User::factory()->create();
    $this->service->logRegistration($user);

    Carbon::setTestNow();
});

// ============================================
// log() generic method tests
// ============================================

test('log generic event captures event name', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'custom.action'
                && $context['event'] === 'custom.action';
        });

    $this->service->log('custom.action');
});

test('log accepts AuditEvent enum', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'auth.login'
                && $context['event'] === 'auth.login';
        });

    $this->service->log(AuditEvent::AUTH_LOGIN);
});

test('log generic event passes context as metadata', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['metadata']['custom_field'] === 'custom_value'
                && $context['metadata']['another_field'] === 123;
        });

    $this->service->log('custom.action', [
        'custom_field' => 'custom_value',
        'another_field' => 123,
    ]);
});

test('log generic event includes default context', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return isset($context['event'])
                && array_key_exists('user_id', $context)
                && array_key_exists('ip', $context)
                && isset($context['timestamp']);
        });

    $this->service->log('test.event');
});

test('log generic event uses auth user id', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === 789;
        });

    $user = User::factory()->create(['id' => 789]);
    Auth::login($user);

    $this->service->log('test.event');
});

test('log generic event user id null when not authenticated', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === null;
        });

    $this->service->log('anonymous.action');
});

test('log uses single channel', function () {
    Log::shouldReceive('channel')
        ->with('single')
        ->andReturnSelf();

    Log::shouldReceive('info')
        ->once();

    $this->service->log('test.channel');
});

// ============================================
// IP anonymization tests
// ============================================

test('persist anonymizes IP for non-security events when config enabled', function () {
    config(['services.audit.ip_anonymization' => true]);

    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            // Non-auth event should have anonymized IP (last octet zeroed)
            return $message === 'profile.updated'
                && $context['ip'] === '192.168.1.0';
        });

    // Simulate a request with a known IP
    $this->app['request']->server->set('REMOTE_ADDR', '192.168.1.42');

    $user = User::factory()->create();
    Auth::login($user);

    $this->service->log('profile.updated', ['field' => 'name']);
});

test('persist keeps full IP for auth events even when anonymization enabled', function () {
    config(['services.audit.ip_anonymization' => true]);

    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            // Auth event should retain full IP
            return $message === 'auth.login'
                && $context['ip'] === '192.168.1.42';
        });

    $this->app['request']->server->set('REMOTE_ADDR', '192.168.1.42');

    $user = User::factory()->create();
    $this->service->logLogin($user);
});

test('persist does not anonymize IP when config disabled', function () {
    config(['services.audit.ip_anonymization' => false]);

    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['ip'] === '10.0.5.123';
        });

    $this->app['request']->server->set('REMOTE_ADDR', '10.0.5.123');

    $user = User::factory()->create();
    Auth::login($user);

    $this->service->log('profile.updated');
});

// ============================================
// Explicit user_id in log() context
// ============================================

test('log() uses explicit user_id from context when provided', function () {
    expectLogChannel();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) {
            return $context['user_id'] === 99
                && ! array_key_exists('user_id', $context['metadata']);
        });

    // No Auth::login — but explicit user_id should be honored
    $this->service->log('audit.explicit_actor', ['user_id' => 99, 'detail' => 'x']);
});

test('log() falls back to Auth::id() when user_id absent from context', function () {
    expectLogChannel();

    $user = User::factory()->create();
    Auth::login($user);

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) use ($user) {
            return $context['user_id'] === $user->id;
        });

    $this->service->log('audit.auth_actor', ['detail' => 'x']);
});
