<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip if API tokens feature is disabled
        if (! config('features.api_tokens.enabled', true)) {
            $this->markTestSkipped('API tokens feature is disabled.');
        }
    }

    public function test_deleting_non_existent_token_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/tokens/99999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Token not found.']);
    }

    public function test_deleting_existing_token_returns_success(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        $tokenId = $token->accessToken->id;

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/tokens/{$tokenId}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }
}
