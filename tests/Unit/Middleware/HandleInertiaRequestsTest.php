<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    private HandleInertiaRequests $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = app(HandleInertiaRequests::class);
    }

    // ============================================
    // rootView tests
    // ============================================

    public function test_root_view_is_app(): void
    {
        $reflection = new \ReflectionClass($this->middleware);
        $property = $reflection->getProperty('rootView');
        $property->setAccessible(true);

        $this->assertEquals('app', $property->getValue($this->middleware));
    }

    // ============================================
    // version tests
    // ============================================

    public function test_version_returns_string_or_null(): void
    {
        $request = Request::create('/test');
        $version = $this->middleware->version($request);

        $this->assertTrue($version === null || is_string($version));
    }

    // ============================================
    // share - auth tests
    // ============================================

    public function test_share_includes_auth_key(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('auth', $shared);
    }

    public function test_share_includes_null_user_when_not_authenticated(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('user', $shared['auth']);
        $this->assertNull($shared['auth']['user']);
    }

    public function test_share_includes_user_when_authenticated(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);

        $shared = $this->middleware->share($request);

        $this->assertNotNull($shared['auth']['user']);
        $this->assertEquals($user->id, $shared['auth']['user']['id']);
        $this->assertEquals($user->name, $shared['auth']['user']['name']);
        $this->assertEquals($user->email, $shared['auth']['user']['email']);
        $this->assertArrayHasKey('email_verified_at', $shared['auth']['user']);
        $this->assertArrayHasKey('has_password', $shared['auth']['user']);
    }

    // ============================================
    // share - flash tests
    // ============================================

    public function test_share_includes_flash_key(): void
    {
        $request = Request::create('/test');
        $request->setLaravelSession(app('session.store'));
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('flash', $shared);
    }

    public function test_share_includes_flash_success(): void
    {
        $request = Request::create('/test');
        $request->setLaravelSession(app('session.store'));
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('success', $shared['flash']);
    }

    public function test_share_includes_flash_error(): void
    {
        $request = Request::create('/test');
        $request->setLaravelSession(app('session.store'));
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('error', $shared['flash']);
    }

    public function test_share_includes_flash_warning(): void
    {
        $request = Request::create('/test');
        $request->setLaravelSession(app('session.store'));
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('warning', $shared['flash']);
    }

    public function test_share_includes_flash_info(): void
    {
        $request = Request::create('/test');
        $request->setLaravelSession(app('session.store'));
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('info', $shared['flash']);
    }

    public function test_flash_success_returns_session_value(): void
    {
        $request = Request::create('/test');
        $session = app('session.store');
        $session->flash('success', 'Operation completed');
        $request->setLaravelSession($session);

        $shared = $this->middleware->share($request);

        // Flash values are closures, call them
        $successValue = is_callable($shared['flash']['success'])
            ? $shared['flash']['success']()
            : $shared['flash']['success'];

        $this->assertEquals('Operation completed', $successValue);
    }

    public function test_flash_error_returns_session_value(): void
    {
        $request = Request::create('/test');
        $session = app('session.store');
        $session->flash('error', 'Something went wrong');
        $request->setLaravelSession($session);

        $shared = $this->middleware->share($request);

        $errorValue = is_callable($shared['flash']['error'])
            ? $shared['flash']['error']()
            : $shared['flash']['error'];

        $this->assertEquals('Something went wrong', $errorValue);
    }

    // ============================================
    // share - features tests
    // ============================================

    public function test_share_includes_features_key(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('features', $shared);
    }

    public function test_share_includes_billing_feature(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('billing', $shared['features']);
    }

    public function test_share_includes_social_auth_feature(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('socialAuth', $shared['features']);
    }

    public function test_share_includes_email_verification_feature(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('emailVerification', $shared['features']);
    }

    public function test_share_includes_api_tokens_feature(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('apiTokens', $shared['features']);
    }

    public function test_share_includes_user_settings_feature(): void
    {
        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertArrayHasKey('userSettings', $shared['features']);
    }

    public function test_features_billing_reflects_config(): void
    {
        config(['features.billing.enabled' => true]);

        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertTrue($shared['features']['billing']);

        config(['features.billing.enabled' => false]);
        $shared = $this->middleware->share($request);

        $this->assertFalse($shared['features']['billing']);
    }

    public function test_features_social_auth_reflects_config(): void
    {
        config(['features.social_auth.enabled' => true]);

        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        $this->assertTrue($shared['features']['socialAuth']);

        config(['features.social_auth.enabled' => false]);
        $shared = $this->middleware->share($request);

        $this->assertFalse($shared['features']['socialAuth']);
    }

    public function test_features_email_verification_defaults_to_true(): void
    {
        // Clear the config to test default value
        config(['features.email_verification' => []]);

        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        // Default is true based on middleware code
        $this->assertTrue($shared['features']['emailVerification']);
    }

    public function test_features_api_tokens_defaults_to_true(): void
    {
        // Clear the config to test default value
        config(['features.api_tokens' => []]);

        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        // Default is true based on middleware code
        $this->assertTrue($shared['features']['apiTokens']);
    }

    public function test_features_user_settings_defaults_to_true(): void
    {
        // Clear the config to test default value
        config(['features.user_settings' => []]);

        $request = Request::create('/test');
        $shared = $this->middleware->share($request);

        // Default is true based on middleware code
        $this->assertTrue($shared['features']['userSettings']);
    }
}
