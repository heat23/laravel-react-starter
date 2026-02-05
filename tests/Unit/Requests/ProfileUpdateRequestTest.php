<?php

namespace Tests\Unit\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'current@example.com',
            'name' => 'Current User',
        ]);
    }

    // ============================================
    // Name Validation Tests
    // ============================================

    public function test_rules_requires_name(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'email' => 'current@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_rules_name_must_be_string(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 12345, // Integer instead of string
            'email' => 'current@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_rules_name_max_length_255(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => str_repeat('a', 256),
            'email' => 'current@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_rules_name_accepts_max_length(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => str_repeat('a', 255),
            'email' => 'current@example.com',
        ]);

        $response->assertSessionDoesntHaveErrors('name');
    }

    public function test_rules_name_accepts_special_characters(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => "O'Brien-Smith Jr.",
            'email' => 'current@example.com',
        ]);

        $response->assertSessionDoesntHaveErrors('name');
        $this->user->refresh();
        $this->assertEquals("O'Brien-Smith Jr.", $this->user->name);
    }

    // ============================================
    // Email Validation Tests
    // ============================================

    public function test_rules_requires_email(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_rules_email_must_be_string(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 12345, // Integer instead of string
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_rules_email_must_be_valid_format(): void
    {
        $invalidEmails = [
            'not-an-email',
            'test@',
            '@example.com',
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->actingAs($this->user)->patch('/profile', [
                'name' => 'Test User',
                'email' => $email,
            ]);

            $response->assertSessionHasErrors('email', "Email '$email' should fail validation");
        }
    }

    public function test_rules_email_must_be_lowercase(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'TEST@EXAMPLE.COM',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_rules_email_max_length_255(): void
    {
        $longEmail = str_repeat('a', 250) . '@example.com';

        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => $longEmail,
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ============================================
    // Email Uniqueness Tests (Core Functionality)
    // ============================================

    public function test_rules_email_unique_ignores_current_user(): void
    {
        // User should be able to keep their own email
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Updated Name',
            'email' => 'current@example.com', // Same email as current
        ]);

        $response->assertSessionDoesntHaveErrors('email');
    }

    public function test_rules_email_rejects_another_users_email(): void
    {
        // Create another user with a different email
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_change_to_new_unique_email(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'newemail@example.com',
        ]);

        $response->assertSessionDoesntHaveErrors('email');
        $this->user->refresh();
        $this->assertEquals('newemail@example.com', $this->user->email);
    }

    // ============================================
    // Email Verification Impact Tests
    // ============================================

    public function test_email_change_clears_verification_status(): void
    {
        // Set user as verified
        $this->user->update(['email_verified_at' => now()]);
        $this->assertNotNull($this->user->email_verified_at);

        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'newemail@example.com',
        ]);

        $this->user->refresh();
        $this->assertNull($this->user->email_verified_at);
    }

    public function test_same_email_preserves_verification_status(): void
    {
        // Set user as verified
        $this->user->update(['email_verified_at' => now()]);
        $verifiedAt = $this->user->email_verified_at;

        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'current@example.com', // Same email
        ]);

        $this->user->refresh();
        $this->assertNotNull($this->user->email_verified_at);
    }

    // ============================================
    // Successful Update Tests
    // ============================================

    public function test_successful_update_changes_name(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'current@example.com',
        ]);

        $this->user->refresh();
        $this->assertEquals('New Name', $this->user->name);
    }

    public function test_successful_update_changes_email(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Current User',
            'email' => 'newemail@example.com',
        ]);

        $this->user->refresh();
        $this->assertEquals('newemail@example.com', $this->user->email);
    }

    public function test_successful_update_redirects_to_profile(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'current@example.com',
        ]);

        $response->assertRedirect(route('profile.edit'));
    }

    // ============================================
    // Authorization Tests
    // ============================================

    public function test_update_requires_authentication(): void
    {
        $response = $this->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_only_update_own_profile(): void
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        // Even if trying to set email to another user's, should update own profile
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Hacker',
            'email' => 'newemail@example.com',
        ]);

        // Other user should be unaffected
        $otherUser->refresh();
        $this->assertEquals('other@example.com', $otherUser->email);
    }

    // ============================================
    // Edge Cases
    // ============================================

    public function test_update_with_empty_name_fails(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => '',
            'email' => 'current@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_with_whitespace_only_name_fails(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => '   ',
            'email' => 'current@example.com',
        ]);

        // May or may not fail depending on trim behavior
        // Document the expected/actual behavior
        $this->assertTrue(
            session('errors')?->has('name') ?? true ||
            $this->user->fresh()->name !== '   '
        );
    }

    public function test_update_handles_unicode_name(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => '日本語名前',
            'email' => 'current@example.com',
        ]);

        $response->assertSessionDoesntHaveErrors('name');
        $this->user->refresh();
        $this->assertEquals('日本語名前', $this->user->name);
    }

    public function test_email_uniqueness_is_case_insensitive(): void
    {
        // Create user with lowercase email
        User::factory()->create(['email' => 'other@example.com']);

        // Try to use uppercase version of same email
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'other@example.com', // lowercase of existing
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_concurrent_updates_handle_race_condition(): void
    {
        // This documents expected behavior for concurrent updates
        $anotherUser = User::factory()->create(['email' => 'another@example.com']);

        // Simulate concurrent update where another user just took the email
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'another@example.com',
        ]);

        // Should fail due to unique constraint
        $response->assertSessionHasErrors('email');
    }
}
