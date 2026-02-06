<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ApiTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['features.api_tokens.enabled' => true]);
        RateLimiter::clear('127.0.0.1');
    }

    // ============================================
    // Index tests
    // ============================================

    public function test_index_returns_empty_array_when_no_tokens(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tokens');

        $response->assertOk()
            ->assertJson([]);
    }

    public function test_index_lists_all_user_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('Token 1');
        $user->createToken('Token 2');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tokens');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_index_returns_token_details(): void
    {
        $user = User::factory()->create();
        $user->createToken('My Token', ['read', 'write']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tokens');

        $response->assertOk()
            ->assertJsonStructure([
                ['id', 'name', 'abilities', 'last_used_at', 'created_at'],
            ])
            ->assertJsonPath('0.name', 'My Token')
            ->assertJsonPath('0.abilities', ['read', 'write']);
    }

    public function test_index_does_not_show_other_users_tokens(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->createToken('User 1 Token');
        $user2->createToken('User 2 Token');

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson('/api/tokens');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'User 1 Token');
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/tokens');

        $response->assertUnauthorized();
    }

    // ============================================
    // Store tests
    // ============================================

    public function test_store_creates_new_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tokens', [
                'name' => 'New Token',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'id']);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'New Token',
        ]);
    }

    public function test_store_returns_plain_text_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tokens', [
                'name' => 'New Token',
            ]);

        $response->assertOk();

        $token = $response->json('token');
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);
    }

    public function test_store_creates_token_with_abilities(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tokens', [
                'name' => 'Read Only Token',
                'abilities' => ['read'],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Read Only Token',
            'abilities' => json_encode(['read']),
        ]);
    }

    public function test_store_defaults_to_all_abilities(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tokens', [
                'name' => 'Full Access Token',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Full Access Token',
            'abilities' => json_encode(['*']),
        ]);
    }

    public function test_store_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tokens', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_name_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tokens', [
                'name' => str_repeat('a', 256),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/tokens', [
            'name' => 'New Token',
        ]);

        $response->assertUnauthorized();
    }

    // ============================================
    // Destroy tests
    // ============================================

    public function test_destroy_deletes_own_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('My Token');
        $tokenId = $token->accessToken->id;

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/tokens/{$tokenId}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_destroy_cannot_delete_other_users_token(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $token = $user2->createToken('User 2 Token');
        $tokenId = $token->accessToken->id;

        $response = $this->actingAs($user1, 'sanctum')
            ->deleteJson("/api/tokens/{$tokenId}");

        $response->assertNotFound()
            ->assertJson(['message' => 'Token not found.']);

        // Token should still exist
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/tokens/99999');

        $response->assertNotFound()
            ->assertJson(['message' => 'Token not found.']);
    }

    public function test_destroy_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/tokens/1');

        $response->assertUnauthorized();
    }

    // ============================================
    // Rate limiting tests
    // ============================================

    public function test_token_creation_is_rate_limited(): void
    {
        $user = User::factory()->create();

        // Make 20 requests (the limit)
        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/tokens', [
                    'name' => "Token {$i}",
                ]);
        }

        // 21st request should be rate limited
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tokens', [
                'name' => 'One Too Many',
            ]);

        $response->assertStatus(429);
    }
}
