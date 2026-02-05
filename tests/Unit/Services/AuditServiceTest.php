<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditService();
    }

    // ============================================
    // logLogin() tests
    // ============================================

    public function test_log_login_captures_user_id(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'User logged in'
                    && $context['user_id'] === 1;
            });

        $user = User::factory()->create(['id' => 1]);
        $this->service->logLogin($user);
    }

    public function test_log_login_captures_email(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['email'] === 'test@example.com';
            });

        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->service->logLogin($user);
    }

    public function test_log_login_captures_event_name(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['event'] === 'auth.login';
            });

        $user = User::factory()->create();
        $this->service->logLogin($user);
    }

    public function test_log_login_captures_ip_address(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return isset($context['ip']);
            });

        $user = User::factory()->create();
        $this->service->logLogin($user);
    }

    public function test_log_login_captures_user_agent(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return array_key_exists('user_agent', $context);
            });

        $user = User::factory()->create();
        $this->service->logLogin($user);
    }

    public function test_log_login_captures_timestamp(): void
    {
        Carbon::setTestNow('2024-01-15 12:00:00');

        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return isset($context['timestamp'])
                    && str_contains($context['timestamp'], '2024-01-15');
            });

        $user = User::factory()->create();
        $this->service->logLogin($user);

        Carbon::setTestNow();
    }

    public function test_log_login_uses_auth_user_when_not_provided(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === 1
                    && $context['email'] === 'auth@example.com';
            });

        $user = User::factory()->create(['id' => 1, 'email' => 'auth@example.com']);
        Auth::login($user);

        $this->service->logLogin(); // No user passed
    }

    public function test_log_login_handles_null_user(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === null
                    && $context['email'] === null;
            });

        $this->service->logLogin(null);
    }

    public function test_log_login_uses_single_channel(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once();

        $user = User::factory()->create();
        $this->service->logLogin($user);
    }

    // ============================================
    // logLogout() tests
    // ============================================

    public function test_log_logout_captures_correct_event_name(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'User logged out'
                    && $context['event'] === 'auth.logout';
            });

        $user = User::factory()->create();
        $this->service->logLogout($user);
    }

    public function test_log_logout_captures_user_id(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === 123;
            });

        $user = User::factory()->create(['id' => 123]);
        $this->service->logLogout($user);
    }

    public function test_log_logout_captures_email(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['email'] === 'logout@example.com';
            });

        $user = User::factory()->create(['email' => 'logout@example.com']);
        $this->service->logLogout($user);
    }

    public function test_log_logout_uses_auth_user_when_not_provided(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === 1;
            });

        $user = User::factory()->create(['id' => 1]);
        Auth::login($user);

        $this->service->logLogout();
    }

    public function test_log_logout_captures_ip(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return array_key_exists('ip', $context);
            });

        $user = User::factory()->create();
        $this->service->logLogout($user);
    }

    public function test_log_logout_captures_timestamp(): void
    {
        Carbon::setTestNow('2024-06-01 08:30:00');

        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($context['timestamp'], '2024-06-01');
            });

        $user = User::factory()->create();
        $this->service->logLogout($user);

        Carbon::setTestNow();
    }

    // ============================================
    // logRegistration() tests
    // ============================================

    public function test_log_registration_captures_correct_event_name(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'User registered'
                    && $context['event'] === 'auth.register';
            });

        $user = User::factory()->create();
        $this->service->logRegistration($user);
    }

    public function test_log_registration_captures_user_id(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === 456;
            });

        $user = User::factory()->create(['id' => 456]);
        $this->service->logRegistration($user);
    }

    public function test_log_registration_captures_email(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['email'] === 'newuser@example.com';
            });

        $user = User::factory()->create(['email' => 'newuser@example.com']);
        $this->service->logRegistration($user);
    }

    public function test_log_registration_captures_signup_source(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['signup_source'] === 'google';
            });

        $user = User::factory()->create(['signup_source' => 'google']);
        $this->service->logRegistration($user);
    }

    public function test_log_registration_defaults_signup_source_to_direct(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['signup_source'] === 'direct';
            });

        $user = User::factory()->create(['signup_source' => null]);
        $this->service->logRegistration($user);
    }

    public function test_log_registration_captures_ip(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return array_key_exists('ip', $context);
            });

        $user = User::factory()->create();
        $this->service->logRegistration($user);
    }

    public function test_log_registration_captures_timestamp(): void
    {
        Carbon::setTestNow('2024-12-25 00:00:00');

        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($context['timestamp'], '2024-12-25');
            });

        $user = User::factory()->create();
        $this->service->logRegistration($user);

        Carbon::setTestNow();
    }

    // ============================================
    // log() generic method tests
    // ============================================

    public function test_log_generic_event_captures_event_name(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'custom.action'
                    && $context['event'] === 'custom.action';
            });

        $this->service->log('custom.action');
    }

    public function test_log_generic_event_merges_custom_context(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['custom_field'] === 'custom_value'
                    && $context['another_field'] === 123;
            });

        $this->service->log('custom.action', [
            'custom_field' => 'custom_value',
            'another_field' => 123,
        ]);
    }

    public function test_log_generic_event_includes_default_context(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return isset($context['event'])
                    && array_key_exists('user_id', $context)
                    && array_key_exists('ip', $context)
                    && isset($context['timestamp']);
            });

        $this->service->log('test.event');
    }

    public function test_log_generic_event_custom_context_overrides_defaults(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Custom timestamp should override default
                return $context['timestamp'] === 'custom-timestamp';
            });

        $this->service->log('test.event', [
            'timestamp' => 'custom-timestamp',
        ]);
    }

    public function test_log_generic_event_uses_auth_user_id(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === 789;
            });

        $user = User::factory()->create(['id' => 789]);
        Auth::login($user);

        $this->service->log('test.event');
    }

    public function test_log_generic_event_user_id_null_when_not_authenticated(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === null;
            });

        $this->service->log('anonymous.action');
    }

    public function test_log_uses_single_channel(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once();

        $this->service->log('test.channel');
    }
}
