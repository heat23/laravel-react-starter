<?php

namespace Tests\Unit\Models;

use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable features and create tables if they don't exist
        config(['features.social_auth.enabled' => true]);
        config(['features.user_settings.enabled' => true]);

        if (!\Schema::hasTable('social_accounts')) {
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

        if (!\Schema::hasTable('user_settings')) {
            \Schema::create('user_settings', function ($table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('key', 64);
                $table->text('value')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'key']);
            });
        }
    }

    // ============================================
    // hasPassword() tests
    // ============================================

    public function test_has_password_returns_true_when_password_is_set(): void
    {
        $user = User::factory()->create(['password' => 'hashed-password']);

        $this->assertTrue($user->hasPassword());
    }

    public function test_has_password_returns_false_when_password_is_null(): void
    {
        $user = User::factory()->create();
        // Bypass the hashed cast by using the query builder directly
        \DB::table('users')->where('id', $user->id)->update(['password' => null]);
        $user->refresh();

        $this->assertFalse($user->hasPassword());
    }

    public function test_has_password_returns_false_when_password_is_empty_string(): void
    {
        $user = User::factory()->create();
        // Bypass the hashed cast by using the query builder directly
        \DB::table('users')->where('id', $user->id)->update(['password' => '']);
        $user->refresh();

        $this->assertFalse($user->hasPassword());
    }

    // ============================================
    // updateLastLogin() tests
    // ============================================

    public function test_update_last_login_sets_timestamp_to_now(): void
    {
        Carbon::setTestNow('2024-01-15 14:30:00');

        $user = User::factory()->create(['last_login_at' => null]);

        $user->updateLastLogin();

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertEquals('2024-01-15 14:30:00', $user->last_login_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_update_last_login_updates_existing_timestamp(): void
    {
        Carbon::setTestNow('2024-01-15 14:30:00');

        $user = User::factory()->create(['last_login_at' => Carbon::parse('2024-01-01 00:00:00')]);

        $user->updateLastLogin();

        $user->refresh();
        $this->assertEquals('2024-01-15 14:30:00', $user->last_login_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_update_last_login_persists_to_database(): void
    {
        Carbon::setTestNow('2024-06-01 10:00:00');

        $user = User::factory()->create(['last_login_at' => null]);

        $user->updateLastLogin();

        // Fetch fresh from database
        $freshUser = User::find($user->id);
        $this->assertEquals('2024-06-01 10:00:00', $freshUser->last_login_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    // ============================================
    // getSetting() tests
    // ============================================

    public function test_get_setting_returns_value_when_setting_exists(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'theme',
            'value' => 'dark',
        ]);

        $result = $user->getSetting('theme');

        $this->assertEquals('dark', $result);
    }

    public function test_get_setting_returns_default_when_setting_not_found(): void
    {
        $user = User::factory()->create();

        $result = $user->getSetting('nonexistent', 'default-value');

        $this->assertEquals('default-value', $result);
    }

    public function test_get_setting_returns_null_default_when_no_default_provided(): void
    {
        $user = User::factory()->create();

        $result = $user->getSetting('nonexistent');

        $this->assertNull($result);
    }

    public function test_get_setting_decodes_json_values(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'preferences',
            'value' => json_encode(['notifications' => true, 'theme' => 'dark']),
        ]);

        $result = $user->getSetting('preferences');

        $this->assertIsArray($result);
        $this->assertTrue($result['notifications']);
        $this->assertEquals('dark', $result['theme']);
    }

    // ============================================
    // setSetting() tests
    // ============================================

    public function test_set_setting_creates_new_setting(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseMissing('user_settings', [
            'user_id' => $user->id,
            'key' => 'timezone',
        ]);

        $user->setSetting('timezone', 'America/New_York');

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'timezone',
            'value' => 'America/New_York',
        ]);
    }

    public function test_set_setting_updates_existing_setting(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'theme',
            'value' => 'light',
        ]);

        $user->setSetting('theme', 'dark');

        $this->assertDatabaseCount('user_settings', 1);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'theme',
            'value' => 'dark',
        ]);
    }

    public function test_set_setting_encodes_array_as_json(): void
    {
        $user = User::factory()->create();

        $user->setSetting('preferences', ['email' => true, 'push' => false]);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'preferences',
            'value' => '{"email":true,"push":false}',
        ]);
    }

    public function test_set_setting_encodes_boolean_as_json(): void
    {
        $user = User::factory()->create();

        $user->setSetting('notifications', true);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'notifications',
            'value' => 'true',
        ]);
    }

    public function test_set_setting_returns_setting_model(): void
    {
        $user = User::factory()->create();

        $result = $user->setSetting('theme', 'dark');

        $this->assertInstanceOf(UserSetting::class, $result);
        $this->assertEquals('theme', $result->key);
    }

    // ============================================
    // Relationship tests
    // ============================================

    public function test_user_has_many_social_accounts(): void
    {
        $user = User::factory()->create();

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'token1',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github-456',
            'token' => 'token2',
        ]);

        $this->assertCount(2, $user->socialAccounts);
        $this->assertInstanceOf(SocialAccount::class, $user->socialAccounts->first());
    }

    public function test_user_has_many_settings(): void
    {
        $user = User::factory()->create();

        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'theme',
            'value' => 'dark',
        ]);

        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'timezone',
            'value' => 'UTC',
        ]);

        $this->assertCount(2, $user->settings);
        $this->assertInstanceOf(UserSetting::class, $user->settings->first());
    }

    public function test_user_social_accounts_returns_empty_collection_when_none(): void
    {
        $user = User::factory()->create();

        $this->assertCount(0, $user->socialAccounts);
        $this->assertTrue($user->socialAccounts->isEmpty());
    }

    // ============================================
    // Attribute casting tests
    // ============================================

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create(['email_verified_at' => '2024-01-15 12:00:00']);

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
    }

    public function test_last_login_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create(['last_login_at' => '2024-01-15 12:00:00']);

        $this->assertInstanceOf(Carbon::class, $user->last_login_at);
    }

    public function test_trial_ends_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create(['trial_ends_at' => '2024-01-15 12:00:00']);

        $this->assertInstanceOf(Carbon::class, $user->trial_ends_at);
    }

    public function test_password_is_hashed_when_set(): void
    {
        $user = User::factory()->create(['password' => 'plaintext-password']);

        // Password should not be stored as plaintext
        $this->assertNotEquals('plaintext-password', $user->password);
        // Should be a bcrypt hash
        $this->assertTrue(str_starts_with($user->password, '$2y$'));
    }

    // ============================================
    // Hidden attributes tests
    // ============================================

    public function test_password_is_hidden_in_serialization(): void
    {
        $user = User::factory()->create(['password' => 'secret']);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_remember_token_is_hidden_in_serialization(): void
    {
        $user = User::factory()->create(['remember_token' => 'secret-token']);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('remember_token', $array);
    }

    // ============================================
    // Fillable attributes tests
    // ============================================

    public function test_name_is_fillable(): void
    {
        $user = User::factory()->create();

        $user->update(['name' => 'New Name']);

        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_email_is_fillable(): void
    {
        $user = User::factory()->create();

        $user->update(['email' => 'new@example.com']);

        $this->assertEquals('new@example.com', $user->fresh()->email);
    }

    public function test_signup_source_is_fillable(): void
    {
        $user = User::factory()->create();

        $user->update(['signup_source' => 'google']);

        $this->assertEquals('google', $user->fresh()->signup_source);
    }

    public function test_last_login_at_is_fillable(): void
    {
        $user = User::factory()->create();
        $timestamp = now();

        $user->update(['last_login_at' => $timestamp]);

        $this->assertEquals($timestamp->format('Y-m-d H:i:s'), $user->fresh()->last_login_at->format('Y-m-d H:i:s'));
    }
}
