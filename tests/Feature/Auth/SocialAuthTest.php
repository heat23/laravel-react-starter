<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

/**
 * Social Authentication Feature Tests
 *
 * Tests the SocialAuthController's behavior.
 *
 * Note: These tests require the social_auth feature to be enabled in the environment
 * (FEATURE_SOCIAL_AUTH=true) for routes to be registered. Tests that require
 * the routes will be skipped if they're not registered.
 */
class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected bool $routesRegistered = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Check if social auth routes are registered
        $this->routesRegistered = \Route::has('social.disconnect');

        // Create social_accounts table if it doesn't exist
        if (! \Schema::hasTable('social_accounts')) {
            \Schema::create('social_accounts', function ($table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('provider', 32);
                $table->string('provider_id');
                $table->text('token')->nullable();
                $table->text('refresh_token')->nullable();
                $table->timestamp('token_expires_at')->nullable();
                $table->timestamps();
                $table->unique(['provider', 'provider_id']);
                $table->index(['user_id', 'provider']);
            });
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Skip test if social auth routes are not registered.
     */
    protected function skipIfRoutesNotRegistered(): void
    {
        if (! $this->routesRegistered) {
            $this->markTestSkipped('Social auth routes are not registered. Enable FEATURE_SOCIAL_AUTH=true.');
        }
    }

    // ============================================
    // Route registration tests
    // ============================================

    public function test_social_auth_routes_exist_when_feature_enabled(): void
    {
        // This test documents the expected behavior
        // Routes are registered at boot time based on config('features.social_auth.enabled')
        if ($this->routesRegistered) {
            $this->assertTrue(\Route::has('social.redirect'));
            $this->assertTrue(\Route::has('social.callback'));
            $this->assertTrue(\Route::has('social.disconnect'));
        } else {
            $this->assertFalse(\Route::has('social.redirect'));
            $this->assertFalse(\Route::has('social.callback'));
            $this->assertFalse(\Route::has('social.disconnect'));
        }
    }

    // ============================================
    // callback() tests - require routes to be registered
    // ============================================

    public function test_regenerates_session_after_social_auth_login(): void
    {
        $this->skipIfRoutesNotRegistered();

        if (! class_exists(Socialite::class)) {
            $this->markTestSkipped('Socialite package not installed.');
        }

        $user = User::factory()->create();

        $socialUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-123');
        $socialUser->shouldReceive('getEmail')->andReturn($user->email);
        $socialUser->shouldReceive('getName')->andReturn($user->name);
        $socialUser->shouldReceive('getAvatar')->andReturn(null);

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($driver);

        // Capture session ID before callback
        $sessionIdBefore = session()->getId();

        $response = $this->get(route('social.callback', 'google'));

        // Session should have been regenerated (new ID)
        $sessionIdAfter = session()->getId();
        $this->assertNotEquals($sessionIdBefore, $sessionIdAfter);
        $this->assertAuthenticatedAs($user);
    }

    // ============================================
    // disconnect() tests - require routes to be registered
    // ============================================

    public function test_disconnect_removes_social_account(): void
    {
        $this->skipIfRoutesNotRegistered();

        $user = User::factory()->create(['password' => bcrypt('password')]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
        ]);

        $this->assertDatabaseCount('social_accounts', 1);

        $response = $this->actingAs($user)->delete(route('social.disconnect', 'google'));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_disconnect_fails_when_no_password_and_last_social_account(): void
    {
        $this->skipIfRoutesNotRegistered();

        $user = User::factory()->create();
        \DB::table('users')->where('id', $user->id)->update(['password' => null]);
        $user->refresh();

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
        ]);

        $response = $this->actingAs($user)->delete(route('social.disconnect', 'google'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_disconnect_succeeds_when_has_password(): void
    {
        $this->skipIfRoutesNotRegistered();

        $user = User::factory()->create(['password' => bcrypt('password')]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
        ]);

        $response = $this->actingAs($user)->delete(route('social.disconnect', 'google'));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_disconnect_succeeds_when_multiple_social_accounts(): void
    {
        $this->skipIfRoutesNotRegistered();

        $user = User::factory()->create();
        \DB::table('users')->where('id', $user->id)->update(['password' => null]);
        $user->refresh();

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'google-token',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github-456',
            'token' => 'github-token',
        ]);

        $response = $this->actingAs($user)->delete(route('social.disconnect', 'google'));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertDatabaseHas('social_accounts', ['provider' => 'github']);
    }

    public function test_disconnect_requires_authentication(): void
    {
        $this->skipIfRoutesNotRegistered();

        $response = $this->delete(route('social.disconnect', 'google'));

        $response->assertRedirect(route('login'));
    }

    public function test_utm_data_persisted_to_user_settings_for_new_social_registration(): void
    {
        $this->skipIfRoutesNotRegistered();

        if (! class_exists(Socialite::class)) {
            $this->markTestSkipped('Socialite package not installed.');
        }

        $socialUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-new-'.uniqid());
        $socialUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
        $socialUser->shouldReceive('getName')->andReturn('New User');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($driver);

        $response = $this->withSession(['utm_data' => [
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'launch',
        ]])->get(route('social.callback', 'google'));

        $response->assertRedirect();

        $user = User::where('email', 'newuser@example.com')->firstOrFail();

        $this->assertEquals('google', UserSetting::getValue($user->id, 'utm_source'));
        $this->assertEquals('cpc', UserSetting::getValue($user->id, 'utm_medium'));
        $this->assertEquals('launch', UserSetting::getValue($user->id, 'utm_campaign'));
    }

    public function test_utm_data_not_persisted_for_existing_social_login(): void
    {
        $this->skipIfRoutesNotRegistered();

        if (! class_exists(Socialite::class)) {
            $this->markTestSkipped('Socialite package not installed.');
        }

        $user = User::factory()->create();

        $socialUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-existing-'.uniqid());
        $socialUser->shouldReceive('getEmail')->andReturn($user->email);
        $socialUser->shouldReceive('getName')->andReturn($user->name);
        $socialUser->shouldReceive('getAvatar')->andReturn(null);

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($driver);

        $this->withSession(['utm_data' => ['utm_source' => 'twitter']])
            ->get(route('social.callback', 'google'));

        // Existing users should not have UTM overwritten
        $this->assertNull(UserSetting::getValue($user->id, 'utm_source'));
    }
}
