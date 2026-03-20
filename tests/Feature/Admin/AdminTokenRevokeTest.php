<?php

use App\Models\User;

beforeEach(function () {
    config(['features.api_tokens.enabled' => true]);
    registerAdminRoutes();
});

it('admin can view the tokens list page', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->createToken('test-token');

    $this->actingAs($admin)
        ->get('/admin/tokens/list')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Tokens/Index'));
});

it('admin can revoke a specific token', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();
    $token = $user->createToken('api-key')->accessToken;

    $this->assertDatabaseHas('personal_access_tokens', ['name' => 'api-key']);

    $this->actingAs($admin)
        ->delete("/admin/tokens/{$token->id}")
        ->assertRedirect('/admin/tokens/list');

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
});

it('regular admin cannot revoke tokens', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $user = User::factory()->create();
    $token = $user->createToken('api-key')->accessToken;

    $this->actingAs($admin)
        ->delete("/admin/tokens/{$token->id}")
        ->assertForbidden();
});

it('admin tokens list paginates correctly', function () {
    $admin = User::factory()->admin()->create();
    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
        $user->createToken('key-1');
        $user->createToken('key-2');
    }

    $response = $this->actingAs($admin)->get('/admin/tokens/list');
    $tokens = $response->inertia()->prop('tokens.data');
    expect(count($tokens))->toBeGreaterThanOrEqual(1);
});

it('tokens list can be searched by user email', function () {
    $admin = User::factory()->admin()->create();
    $userA = User::factory()->create(['email' => 'alice@example.com']);
    $userB = User::factory()->create(['email' => 'bob@example.com']);
    $userA->createToken('alice-token');
    $userB->createToken('bob-token');

    $response = $this->actingAs($admin)->get('/admin/tokens/list?search=alice');
    $tokens = $response->inertia()->prop('tokens.data');
    $names = collect($tokens)->pluck('token_name');
    expect($names)->toContain('alice-token');
    expect($names)->not->toContain('bob-token');
});

it('returns 404 when revoking non-existent token', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete('/admin/tokens/999999')
        ->assertNotFound();
});
