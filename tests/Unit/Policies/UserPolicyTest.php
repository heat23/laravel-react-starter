<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy;
    }

    public function test_user_can_update_their_own_profile(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->update($user, $user));
    }

    public function test_user_cannot_update_another_users_profile(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->assertFalse($this->policy->update($user, $otherUser));
    }

    public function test_user_can_delete_their_own_account(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->delete($user, $user));
    }

    public function test_user_cannot_delete_another_users_account(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->assertFalse($this->policy->delete($user, $otherUser));
    }
}
