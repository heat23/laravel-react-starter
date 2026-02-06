<?php

namespace Tests\Unit\Services;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SocialAuthService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialUser;
use Mockery;
use Tests\TestCase;

class SocialAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private SocialAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable social auth feature for tests and create the table if it doesn't exist
        config(['features.social_auth.enabled' => true]);

        // Create the social_accounts table if it doesn't exist
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

        $this->service = new SocialAuthService;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mock Socialite user.
     */
    private function mockSocialUser(array $data = []): SocialUser
    {
        $mock = Mockery::mock(SocialUser::class);

        $mock->shouldReceive('getId')->andReturn($data['id'] ?? 'social-123');
        $mock->shouldReceive('getEmail')->andReturn($data['email'] ?? 'social@example.com');
        $mock->shouldReceive('getName')->andReturn($data['name'] ?? 'Social User');
        $mock->shouldReceive('getNickname')->andReturn($data['nickname'] ?? 'socialuser');
        $mock->shouldReceive('getAvatar')->andReturn($data['avatar'] ?? null);

        // Add public properties for token data
        $mock->token = $data['token'] ?? 'test-access-token';
        $mock->refreshToken = $data['refreshToken'] ?? 'test-refresh-token';
        $mock->expiresIn = $data['expiresIn'] ?? 3600;

        return $mock;
    }

    // ============================================
    // findOrCreateUser() tests
    // ============================================

    public function test_find_or_create_returns_existing_user_when_social_account_exists(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        SocialAccount::create([
            'user_id' => $existingUser->id,
            'provider' => 'google',
            'provider_id' => 'google-12345',
            'token' => 'old-token',
        ]);

        $socialUser = $this->mockSocialUser([
            'id' => 'google-12345',
            'email' => 'different@example.com', // Different email, but same provider_id
        ]);

        $user = $this->service->findOrCreateUser($socialUser, 'google');

        $this->assertEquals($existingUser->id, $user->id);
        $this->assertEquals('existing@example.com', $user->email);
    }

    public function test_find_or_create_returns_existing_user_by_email_without_social_account(): void
    {
        $existingUser = User::factory()->create(['email' => 'test@example.com']);

        $socialUser = $this->mockSocialUser([
            'id' => 'new-provider-id',
            'email' => 'test@example.com',
        ]);

        $user = $this->service->findOrCreateUser($socialUser, 'github');

        $this->assertEquals($existingUser->id, $user->id);
    }

    public function test_find_or_create_creates_new_user_when_no_match(): void
    {
        $socialUser = $this->mockSocialUser([
            'id' => 'brand-new-id',
            'email' => 'newuser@example.com',
            'name' => 'Brand New User',
        ]);

        $this->assertDatabaseCount('users', 0);

        $user = $this->service->findOrCreateUser($socialUser, 'google');

        $this->assertDatabaseCount('users', 1);
        $this->assertEquals('newuser@example.com', $user->email);
        $this->assertEquals('Brand New User', $user->name);
    }

    public function test_find_or_create_uses_name_from_social_user(): void
    {
        $socialUser = $this->mockSocialUser([
            'id' => 'test-id',
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'nickname' => 'johnd',
        ]);

        $user = $this->service->findOrCreateUser($socialUser, 'google');

        $this->assertEquals('John Doe', $user->name);
    }

    public function test_find_or_create_uses_nickname_when_name_is_null(): void
    {
        $mock = Mockery::mock(SocialUser::class);
        $mock->shouldReceive('getId')->andReturn('test-id');
        $mock->shouldReceive('getEmail')->andReturn('test@example.com');
        $mock->shouldReceive('getName')->andReturn(null);
        $mock->shouldReceive('getNickname')->andReturn('coolnickname');
        $mock->shouldReceive('getAvatar')->andReturn(null);
        $mock->token = 'token';
        $mock->refreshToken = null;
        $mock->expiresIn = null;

        $user = $this->service->findOrCreateUser($mock, 'github');

        $this->assertEquals('coolnickname', $user->name);
    }

    public function test_find_or_create_uses_user_default_when_name_and_nickname_null(): void
    {
        $mock = Mockery::mock(SocialUser::class);
        $mock->shouldReceive('getId')->andReturn('test-id');
        $mock->shouldReceive('getEmail')->andReturn('anonymous@example.com');
        $mock->shouldReceive('getName')->andReturn(null);
        $mock->shouldReceive('getNickname')->andReturn(null);
        $mock->shouldReceive('getAvatar')->andReturn(null);
        $mock->token = 'token';
        $mock->refreshToken = null;
        $mock->expiresIn = null;

        $user = $this->service->findOrCreateUser($mock, 'github');

        $this->assertEquals('User', $user->name);
    }

    public function test_find_or_create_sets_email_verified_at(): void
    {
        Carbon::setTestNow('2024-01-15 12:00:00');

        $socialUser = $this->mockSocialUser([
            'id' => 'verified-user',
            'email' => 'verified@example.com',
        ]);

        $user = $this->service->findOrCreateUser($socialUser, 'google');

        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals('2024-01-15 12:00:00', $user->email_verified_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_find_or_create_sets_signup_source_to_provider(): void
    {
        $socialUser = $this->mockSocialUser([
            'id' => 'github-user',
            'email' => 'github@example.com',
        ]);

        $user = $this->service->findOrCreateUser($socialUser, 'github');

        $this->assertEquals('github', $user->signup_source);
    }

    public function test_find_or_create_sets_null_password_for_social_users(): void
    {
        $socialUser = $this->mockSocialUser([
            'id' => 'new-user',
            'email' => 'new@example.com',
        ]);

        $user = $this->service->findOrCreateUser($socialUser, 'google');

        // Social-only users should have null password (no random hash)
        $this->assertNull($user->password);
    }

    public function test_find_or_create_does_not_modify_existing_user_data(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Original Name',
            'signup_source' => 'email',
        ]);

        $socialUser = $this->mockSocialUser([
            'id' => 'new-social-id',
            'email' => 'existing@example.com',
            'name' => 'OAuth Name', // Different name
        ]);

        $user = $this->service->findOrCreateUser($socialUser, 'google');

        // Should not update existing user's data
        $this->assertEquals('Original Name', $user->name);
        $this->assertEquals('email', $user->signup_source);
    }

    // ============================================
    // linkSocialAccount() tests
    // ============================================

    public function test_link_social_account_creates_new_social_account(): void
    {
        $user = User::factory()->create();
        $socialUser = $this->mockSocialUser([
            'id' => 'google-abc123',
            'token' => 'access-token-xyz',
            'refreshToken' => 'refresh-token-abc',
            'expiresIn' => 3600,
        ]);

        $this->assertDatabaseCount('social_accounts', 0);

        $socialAccount = $this->service->linkSocialAccount($user, $socialUser, 'google');

        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertEquals($user->id, $socialAccount->user_id);
        $this->assertEquals('google', $socialAccount->provider);
        $this->assertEquals('google-abc123', $socialAccount->provider_id);
    }

    public function test_link_social_account_updates_existing_social_account(): void
    {
        $user = User::factory()->create();

        // Create existing social account
        $existingAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'old-id',
            'token' => 'old-token',
        ]);

        $socialUser = $this->mockSocialUser([
            'id' => 'new-id',
            'token' => 'new-token',
        ]);

        $socialAccount = $this->service->linkSocialAccount($user, $socialUser, 'google');

        // Should update, not create new
        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertEquals($existingAccount->id, $socialAccount->id);
        $this->assertEquals('new-id', $socialAccount->provider_id);
    }

    public function test_link_social_account_stores_token_and_refresh_token(): void
    {
        $user = User::factory()->create();
        $socialUser = $this->mockSocialUser([
            'id' => 'user-123',
            'token' => 'my-access-token',
            'refreshToken' => 'my-refresh-token',
        ]);

        $socialAccount = $this->service->linkSocialAccount($user, $socialUser, 'google');

        // Tokens are encrypted, so check they're stored and can be decrypted
        $this->assertNotNull($socialAccount->token);
        $this->assertNotNull($socialAccount->refresh_token);

        // Fetch fresh from DB to test encryption/decryption
        $freshAccount = SocialAccount::find($socialAccount->id);
        $this->assertEquals('my-access-token', $freshAccount->token);
        $this->assertEquals('my-refresh-token', $freshAccount->refresh_token);
    }

    public function test_link_social_account_calculates_token_expires_at(): void
    {
        Carbon::setTestNow('2024-01-15 12:00:00');

        $user = User::factory()->create();
        $socialUser = $this->mockSocialUser([
            'id' => 'user-123',
            'expiresIn' => 3600, // 1 hour
        ]);

        $socialAccount = $this->service->linkSocialAccount($user, $socialUser, 'google');

        $this->assertNotNull($socialAccount->token_expires_at);
        $this->assertEquals('2024-01-15 13:00:00', $socialAccount->token_expires_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_link_social_account_handles_null_expires_in(): void
    {
        $user = User::factory()->create();

        $mock = Mockery::mock(SocialUser::class);
        $mock->shouldReceive('getId')->andReturn('user-123');
        $mock->shouldReceive('getEmail')->andReturn('test@example.com');
        $mock->token = 'token';
        $mock->refreshToken = null;
        $mock->expiresIn = null;

        $socialAccount = $this->service->linkSocialAccount($user, $mock, 'github');

        $this->assertNull($socialAccount->token_expires_at);
    }

    public function test_link_social_account_handles_zero_expires_in(): void
    {
        Carbon::setTestNow('2024-01-15 12:00:00');

        $user = User::factory()->create();

        $mock = Mockery::mock(SocialUser::class);
        $mock->shouldReceive('getId')->andReturn('user-123');
        $mock->shouldReceive('getEmail')->andReturn('test@example.com');
        $mock->token = 'token';
        $mock->refreshToken = null;
        $mock->expiresIn = 0;

        $socialAccount = $this->service->linkSocialAccount($user, $mock, 'google');

        // expiresIn of 0 should still calculate to current time
        $this->assertEquals('2024-01-15 12:00:00', $socialAccount->token_expires_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_link_social_account_allows_multiple_providers_for_same_user(): void
    {
        $user = User::factory()->create();

        $googleUser = $this->mockSocialUser(['id' => 'google-123']);
        $githubUser = $this->mockSocialUser(['id' => 'github-456']);

        $this->service->linkSocialAccount($user, $googleUser, 'google');
        $this->service->linkSocialAccount($user, $githubUser, 'github');

        $this->assertDatabaseCount('social_accounts', 2);
        $this->assertEquals(2, $user->socialAccounts()->count());
    }
}
