<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable user_settings feature and create table if it doesn't exist
        config(['features.user_settings.enabled' => true]);

        if (! \Schema::hasTable('user_settings')) {
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
    // getValue() tests
    // ============================================

    public function test_get_value_returns_setting_value(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'theme',
            'value' => 'dark',
        ]);

        $result = UserSetting::getValue($user->id, 'theme');

        $this->assertEquals('dark', $result);
    }

    public function test_get_value_returns_default_when_not_found(): void
    {
        $user = User::factory()->create();

        $result = UserSetting::getValue($user->id, 'nonexistent', 'default-value');

        $this->assertEquals('default-value', $result);
    }

    public function test_get_value_returns_null_default_when_no_default_provided(): void
    {
        $user = User::factory()->create();

        $result = UserSetting::getValue($user->id, 'nonexistent');

        $this->assertNull($result);
    }

    public function test_get_value_decodes_json_values(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'preferences',
            'value' => '{"email":true,"push":false}',
        ]);

        $result = UserSetting::getValue($user->id, 'preferences');

        $this->assertIsArray($result);
        $this->assertTrue($result['email']);
        $this->assertFalse($result['push']);
    }

    public function test_get_value_returns_string_for_non_json(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'name',
            'value' => 'John Doe',
        ]);

        $result = UserSetting::getValue($user->id, 'name');

        $this->assertIsString($result);
        $this->assertEquals('John Doe', $result);
    }

    public function test_get_value_caches_result(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'cached_key',
            'value' => 'cached_value',
        ]);

        // First call should cache
        $result1 = UserSetting::getValue($user->id, 'cached_key');

        // Verify cache key exists
        $cacheKey = "user_setting:{$user->id}:cached_key";
        $this->assertTrue(Cache::has($cacheKey));

        // Second call should use cache
        $result2 = UserSetting::getValue($user->id, 'cached_key');

        $this->assertEquals($result1, $result2);
    }

    public function test_get_value_uses_cached_result(): void
    {
        $user = User::factory()->create();

        // Pre-populate cache
        $cacheKey = "user_setting:{$user->id}:pre_cached";
        Cache::put($cacheKey, 'from-cache', 3600);

        // Should return cached value even without DB record
        $result = UserSetting::getValue($user->id, 'pre_cached');

        $this->assertEquals('from-cache', $result);
    }

    public function test_get_value_decodes_boolean_true(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'enabled',
            'value' => 'true',
        ]);

        $result = UserSetting::getValue($user->id, 'enabled');

        $this->assertTrue($result);
    }

    public function test_get_value_decodes_boolean_false(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'disabled',
            'value' => 'false',
        ]);

        $result = UserSetting::getValue($user->id, 'disabled');

        $this->assertFalse($result);
    }

    public function test_get_value_decodes_integer(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'count',
            'value' => '42',
        ]);

        $result = UserSetting::getValue($user->id, 'count');

        $this->assertEquals(42, $result);
    }

    // ============================================
    // setValue() tests
    // ============================================

    public function test_set_value_creates_new_setting(): void
    {
        $user = User::factory()->create();

        $setting = UserSetting::setValue($user->id, 'new_key', 'new_value');

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'new_key',
            'value' => 'new_value',
        ]);
        $this->assertInstanceOf(UserSetting::class, $setting);
    }

    public function test_set_value_updates_existing_setting(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'existing',
            'value' => 'old_value',
        ]);

        UserSetting::setValue($user->id, 'existing', 'new_value');

        // Assert that the setting was updated (not duplicated)
        // Note: User factory may create onboarding_completed setting, so we check specific key
        expect(UserSetting::where('user_id', $user->id)->where('key', 'existing')->count())->toBe(1);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'existing',
            'value' => 'new_value',
        ]);
    }

    public function test_set_value_encodes_array_as_json(): void
    {
        $user = User::factory()->create();

        UserSetting::setValue($user->id, 'array_key', ['foo' => 'bar', 'baz' => 123]);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'array_key',
            'value' => '{"foo":"bar","baz":123}',
        ]);
    }

    public function test_set_value_encodes_boolean_true_as_json(): void
    {
        $user = User::factory()->create();

        UserSetting::setValue($user->id, 'bool_key', true);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'bool_key',
            'value' => 'true',
        ]);
    }

    public function test_set_value_encodes_boolean_false_as_json(): void
    {
        $user = User::factory()->create();

        UserSetting::setValue($user->id, 'bool_key', false);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'bool_key',
            'value' => 'false',
        ]);
    }

    public function test_set_value_encodes_integer_as_json(): void
    {
        $user = User::factory()->create();

        UserSetting::setValue($user->id, 'int_key', 42);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'int_key',
            'value' => '42',
        ]);
    }

    public function test_set_value_stores_string_directly(): void
    {
        $user = User::factory()->create();

        UserSetting::setValue($user->id, 'string_key', 'hello world');

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'string_key',
            'value' => 'hello world',
        ]);
    }

    public function test_set_value_clears_cache(): void
    {
        $user = User::factory()->create();

        // Pre-populate cache
        $cacheKey = "user_setting:{$user->id}:cached_key";
        Cache::put($cacheKey, 'old_cached_value', 3600);

        // Set new value should clear cache
        UserSetting::setValue($user->id, 'cached_key', 'new_value');

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_set_value_returns_setting_model(): void
    {
        $user = User::factory()->create();

        $result = UserSetting::setValue($user->id, 'test_key', 'test_value');

        $this->assertInstanceOf(UserSetting::class, $result);
        $this->assertEquals('test_key', $result->key);
        $this->assertEquals('test_value', $result->value);
    }

    // ============================================
    // deleteSetting() tests
    // ============================================

    public function test_delete_setting_removes_setting(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'to_delete',
            'value' => 'value',
        ]);

        UserSetting::deleteSetting($user->id, 'to_delete');

        $this->assertDatabaseMissing('user_settings', [
            'user_id' => $user->id,
            'key' => 'to_delete',
        ]);
    }

    public function test_delete_setting_returns_true_when_deleted(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'to_delete',
            'value' => 'value',
        ]);

        $result = UserSetting::deleteSetting($user->id, 'to_delete');

        $this->assertTrue($result);
    }

    public function test_delete_setting_returns_false_when_not_found(): void
    {
        $user = User::factory()->create();

        $result = UserSetting::deleteSetting($user->id, 'nonexistent');

        $this->assertFalse($result);
    }

    public function test_delete_setting_clears_cache(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'cached_key',
            'value' => 'value',
        ]);

        // Pre-populate cache
        $cacheKey = "user_setting:{$user->id}:cached_key";
        Cache::put($cacheKey, 'cached_value', 3600);

        UserSetting::deleteSetting($user->id, 'cached_key');

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_delete_setting_only_deletes_for_specific_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        UserSetting::create([
            'user_id' => $user1->id,
            'key' => 'shared_key',
            'value' => 'user1_value',
        ]);
        UserSetting::create([
            'user_id' => $user2->id,
            'key' => 'shared_key',
            'value' => 'user2_value',
        ]);

        UserSetting::deleteSetting($user1->id, 'shared_key');

        $this->assertDatabaseMissing('user_settings', [
            'user_id' => $user1->id,
            'key' => 'shared_key',
        ]);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user2->id,
            'key' => 'shared_key',
        ]);
    }

    // ============================================
    // Relationship tests
    // ============================================

    public function test_setting_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $setting = UserSetting::create([
            'user_id' => $user->id,
            'key' => 'test',
            'value' => 'value',
        ]);

        $this->assertInstanceOf(User::class, $setting->user);
        $this->assertEquals($user->id, $setting->user->id);
    }

    // ============================================
    // Edge case tests
    // ============================================

    public function test_get_value_handles_empty_string(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'empty',
            'value' => '',
        ]);

        $result = UserSetting::getValue($user->id, 'empty', 'default');

        // Empty string is valid JSON, should decode to empty string
        $this->assertEquals('', $result);
    }

    public function test_set_value_handles_null(): void
    {
        $user = User::factory()->create();

        UserSetting::setValue($user->id, 'null_key', null);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'key' => 'null_key',
            'value' => 'null',
        ]);
    }

    public function test_get_value_returns_null_for_json_null(): void
    {
        $user = User::factory()->create();
        UserSetting::create([
            'user_id' => $user->id,
            'key' => 'null_value',
            'value' => 'null',
        ]);

        $result = UserSetting::getValue($user->id, 'null_value', 'default');

        $this->assertNull($result);
    }
}
