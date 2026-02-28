<?php

use App\Enums\AdminCacheKey;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('invalidates user feature flag cache when deactivated', function () {
    $user = User::factory()->create();
    Cache::put(AdminCacheKey::featureFlagsUser($user->id), ['cached' => true], 300);

    $this->actingAs($this->admin)
        ->patch("/admin/users/{$user->id}/toggle-active");

    expect(Cache::get(AdminCacheKey::featureFlagsUser($user->id)))->toBeNull();
});

it('invalidates user feature flag cache when restored', function () {
    $user = User::factory()->create();
    $user->delete();
    Cache::put(AdminCacheKey::featureFlagsUser($user->id), ['cached' => true], 300);

    $this->actingAs($this->admin)
        ->patch("/admin/users/{$user->id}/toggle-active");

    expect(Cache::get(AdminCacheKey::featureFlagsUser($user->id)))->toBeNull();
});

it('invalidates billing stats cache when user deactivated', function () {
    $user = User::factory()->create();
    Cache::put(AdminCacheKey::BILLING_STATS->value, ['stale' => true], 300);
    Cache::put(AdminCacheKey::TOKENS_STATS->value, ['stale' => true], 300);

    $this->actingAs($this->admin)
        ->patch("/admin/users/{$user->id}/toggle-active");

    expect(Cache::get(AdminCacheKey::BILLING_STATS->value))->toBeNull();
    expect(Cache::get(AdminCacheKey::TOKENS_STATS->value))->toBeNull();
});

it('invalidates user caches during bulk deactivation', function () {
    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
        Cache::put(AdminCacheKey::featureFlagsUser($user->id), ['cached' => true], 300);
    }
    Cache::put(AdminCacheKey::TOKENS_STATS->value, ['stale' => true], 300);

    $this->actingAs($this->admin)
        ->post('/admin/users/bulk-deactivate', [
            'ids' => $users->pluck('id')->toArray(),
        ]);

    foreach ($users as $user) {
        expect(Cache::get(AdminCacheKey::featureFlagsUser($user->id)))->toBeNull();
    }
    expect(Cache::get(AdminCacheKey::TOKENS_STATS->value))->toBeNull();
});
