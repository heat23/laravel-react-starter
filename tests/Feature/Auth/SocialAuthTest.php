<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
