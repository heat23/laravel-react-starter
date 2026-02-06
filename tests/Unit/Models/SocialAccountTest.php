<?php

namespace Tests\Unit\Models;

use App\Models\SocialAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable social auth feature for tests and create the table if it doesn't exist
        config(['features.social_auth.enabled' => true]);

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

    // ============================================
    // isTokenExpired() tests
    // ============================================

    public function test_is_token_expired_returns_false_when_expires_at_is_null(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => null,
        ]);

        $this->assertFalse($socialAccount->isTokenExpired());
    }

    public function test_is_token_expired_returns_true_when_expires_at_is_past(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => now()->subHours(1),
        ]);

        $this->assertTrue($socialAccount->isTokenExpired());
    }

    public function test_is_token_expired_returns_false_when_expires_at_is_future(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => now()->addHours(1),
        ]);

        $this->assertFalse($socialAccount->isTokenExpired());
    }

    public function test_is_token_expired_returns_false_when_expires_at_is_exactly_now(): void
    {
        // Note: Carbon's isPast() returns false when time is exactly now
        // The token is not "past" until after its expiration time
        Carbon::setTestNow('2024-01-15 12:00:00');

        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => Carbon::parse('2024-01-15 12:00:00'),
        ]);

        // isPast() returns false when exactly equal to now
        $this->assertFalse($socialAccount->isTokenExpired());

        Carbon::setTestNow();
    }

    public function test_is_token_expired_returns_true_when_expires_at_is_one_second_ago(): void
    {
        Carbon::setTestNow('2024-01-15 12:00:00');

        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => Carbon::parse('2024-01-15 11:59:59'),
        ]);

        $this->assertTrue($socialAccount->isTokenExpired());

        Carbon::setTestNow();
    }

    public function test_is_token_expired_returns_false_when_expires_at_is_one_second_in_future(): void
    {
        Carbon::setTestNow('2024-01-15 12:00:00');

        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => Carbon::parse('2024-01-15 12:00:01'),
        ]);

        $this->assertFalse($socialAccount->isTokenExpired());

        Carbon::setTestNow();
    }

    // ============================================
    // Relationship tests
    // ============================================

    public function test_social_account_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github-456',
            'token' => 'test-token',
        ]);

        $this->assertInstanceOf(User::class, $socialAccount->user);
        $this->assertEquals($user->id, $socialAccount->user->id);
    }

    public function test_social_account_user_relationship_returns_correct_user(): void
    {
        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        $socialAccount = SocialAccount::create([
            'user_id' => $user2->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
        ]);

        $this->assertEquals('User Two', $socialAccount->user->name);
        $this->assertNotEquals('User One', $socialAccount->user->name);
    }

    // ============================================
    // Attribute casting tests
    // ============================================

    public function test_token_is_encrypted(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'my-secret-token',
        ]);

        // Fetch raw from database
        $rawValue = \DB::table('social_accounts')
            ->where('id', $socialAccount->id)
            ->value('token');

        // Raw value should be encrypted (not the original plaintext)
        $this->assertNotEquals('my-secret-token', $rawValue);

        // But the model should decrypt it
        $freshAccount = SocialAccount::find($socialAccount->id);
        $this->assertEquals('my-secret-token', $freshAccount->token);
    }

    public function test_refresh_token_is_encrypted(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'access-token',
            'refresh_token' => 'my-refresh-token',
        ]);

        // Fetch raw from database
        $rawValue = \DB::table('social_accounts')
            ->where('id', $socialAccount->id)
            ->value('refresh_token');

        // Raw value should be encrypted
        $this->assertNotEquals('my-refresh-token', $rawValue);

        // But the model should decrypt it
        $freshAccount = SocialAccount::find($socialAccount->id);
        $this->assertEquals('my-refresh-token', $freshAccount->refresh_token);
    }

    public function test_token_expires_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => '2024-06-15 14:30:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $socialAccount->token_expires_at);
        $this->assertEquals('2024-06-15 14:30:00', $socialAccount->token_expires_at->format('Y-m-d H:i:s'));
    }

    // ============================================
    // Hidden attributes tests
    // ============================================

    public function test_token_is_hidden_in_serialization(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'secret-token',
        ]);

        $array = $socialAccount->toArray();

        $this->assertArrayNotHasKey('token', $array);
    }

    public function test_refresh_token_is_hidden_in_serialization(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'access-token',
            'refresh_token' => 'secret-refresh-token',
        ]);

        $array = $socialAccount->toArray();

        $this->assertArrayNotHasKey('refresh_token', $array);
    }

    public function test_provider_is_visible_in_serialization(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github-123',
            'token' => 'test-token',
        ]);

        $array = $socialAccount->toArray();

        $this->assertArrayHasKey('provider', $array);
        $this->assertEquals('github', $array['provider']);
    }

    // ============================================
    // Fillable attributes tests
    // ============================================

    public function test_provider_is_fillable(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
        ]);

        $socialAccount->update(['provider' => 'github']);

        $this->assertEquals('github', $socialAccount->fresh()->provider);
    }

    public function test_provider_id_is_fillable(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'old-id',
            'token' => 'test-token',
        ]);

        $socialAccount->update(['provider_id' => 'new-id']);

        $this->assertEquals('new-id', $socialAccount->fresh()->provider_id);
    }

    public function test_token_is_fillable(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'old-token',
        ]);

        $socialAccount->update(['token' => 'new-token']);

        $this->assertEquals('new-token', $socialAccount->fresh()->token);
    }

    public function test_refresh_token_is_fillable(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'refresh_token' => null,
        ]);

        $socialAccount->update(['refresh_token' => 'new-refresh-token']);

        $this->assertEquals('new-refresh-token', $socialAccount->fresh()->refresh_token);
    }

    public function test_token_expires_at_is_fillable(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'token_expires_at' => null,
        ]);

        $newExpiry = now()->addHours(2);
        $socialAccount->update(['token_expires_at' => $newExpiry]);

        $this->assertEquals(
            $newExpiry->format('Y-m-d H:i:s'),
            $socialAccount->fresh()->token_expires_at->format('Y-m-d H:i:s')
        );
    }

    // ============================================
    // Edge cases
    // ============================================

    public function test_social_account_handles_null_refresh_token(): void
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'test-token',
            'refresh_token' => null,
        ]);

        $freshAccount = SocialAccount::find($socialAccount->id);

        $this->assertNull($freshAccount->refresh_token);
    }

    public function test_multiple_social_accounts_for_same_provider(): void
    {
        $user = User::factory()->create();

        // This should work - same user can have multiple accounts from different providers
        $google = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'token' => 'google-token',
        ]);

        $github = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github-456',
            'token' => 'github-token',
        ]);

        $this->assertDatabaseCount('social_accounts', 2);
        $this->assertCount(2, $user->socialAccounts);
    }
}
