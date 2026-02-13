<?php

use App\Models\FeatureFlagOverride;
use App\Models\User;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    ensureFeatureFlagOverridesTableExists();
    clearFeatureFlagOverrides();
    Cache::flush();
});

it('resolves flag from config default when no overrides exist', function () {
    $service = app(FeatureFlagService::class);

    // billing defaults to false in config
    config(['features.billing.enabled' => false]);
    expect($service->resolve('billing'))->toBeFalse();

    // email_verification defaults to true in config
    config(['features.email_verification.enabled' => true]);
    expect($service->resolve('email_verification'))->toBeTrue();
});

it('global override takes precedence over config default', function () {
    $service = app(FeatureFlagService::class);

    // Use email_verification as it's not route-dependent
    config(['features.email_verification.enabled' => false]);
    $service->setGlobalOverride('email_verification', true);

    expect($service->resolve('email_verification'))->toBeTrue();
});

it('per-user override takes precedence over global override', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    // Use email_verification as it's not route-dependent
    config(['features.email_verification.enabled' => false]);
    $service->setGlobalOverride('email_verification', true);
    $service->setUserOverride('email_verification', $user->id, false);

    expect($service->resolve('email_verification', $user))->toBeFalse();
    // Without user context, should still see global override
    expect($service->resolve('email_verification'))->toBeTrue();
});

it('per-user override takes precedence over config default when no global override', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    // Use email_verification as it's not route-dependent
    config(['features.email_verification.enabled' => false]);
    $service->setUserOverride('email_verification', $user->id, true);

    expect($service->resolve('email_verification', $user))->toBeTrue();
    // Without user context, should still see config default
    expect($service->resolve('email_verification'))->toBeFalse();
});

it('removing global override reverts to config default', function () {
    $service = app(FeatureFlagService::class);

    // Use email_verification as it's not route-dependent
    config(['features.email_verification.enabled' => false]);
    $service->setGlobalOverride('email_verification', true);
    expect($service->resolve('email_verification'))->toBeTrue();

    $service->removeGlobalOverride('email_verification');
    expect($service->resolve('email_verification'))->toBeFalse();
});

it('removing user override reverts to global override', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    // Use email_verification as it's not route-dependent
    config(['features.email_verification.enabled' => false]);
    $service->setGlobalOverride('email_verification', true);
    $service->setUserOverride('email_verification', $user->id, false);
    expect($service->resolve('email_verification', $user))->toBeFalse();

    $service->removeUserOverride('email_verification', $user->id);
    expect($service->resolve('email_verification', $user))->toBeTrue();
});

it('removing user override reverts to config default when no global override', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    // Use email_verification as it's not route-dependent
    config(['features.email_verification.enabled' => false]);
    $service->setUserOverride('email_verification', $user->id, true);
    expect($service->resolve('email_verification', $user))->toBeTrue();

    $service->removeUserOverride('email_verification', $user->id);
    expect($service->resolve('email_verification', $user))->toBeFalse();
});

it('rejects unknown flag names with InvalidArgumentException', function () {
    $service = app(FeatureFlagService::class);

    expect(fn () => $service->resolve('unknown_flag'))
        ->toThrow(InvalidArgumentException::class, 'Unknown feature flag: unknown_flag');
});

it('prevents globally disabling protected flags', function () {
    $service = app(FeatureFlagService::class);

    expect(fn () => $service->setGlobalOverride('admin', false))
        ->toThrow(RuntimeException::class, 'Cannot override protected flag: admin');
});

it('prevents per-user overrides on protected flags', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    expect(fn () => $service->setUserOverride('admin', $user->id, true))
        ->toThrow(RuntimeException::class, 'Cannot override protected flag: admin');
});

it('returns false for route-dependent flag when env is false regardless of DB override', function () {
    $service = app(FeatureFlagService::class);

    // Route-dependent flags like billing can't be enabled via DB if env is false
    // (routes won't exist at boot time)
    config(['features.billing.enabled' => false]);

    // Manually create a DB override to simulate the edge case
    FeatureFlagOverride::create([
        'flag' => 'billing',
        'user_id' => null,
        'enabled' => true,
    ]);

    // Despite the DB override, should return false because routes aren't registered
    expect($service->resolve('billing'))->toBeFalse();
});

it('returns config defaults when database table does not exist', function () {
    // Drop the table temporarily
    Schema::dropIfExists('feature_flag_overrides');

    $service = app(FeatureFlagService::class);

    config(['features.billing.enabled' => true]);
    expect($service->resolve('billing'))->toBeTrue();

    config(['features.billing.enabled' => false]);
    expect($service->resolve('billing'))->toBeFalse();

    // Recreate for other tests
    ensureFeatureFlagOverridesTableExists();
});

it('resolveAll returns all defined flags', function () {
    $service = app(FeatureFlagService::class);

    $result = $service->resolveAll();

    // Should have all defined flags
    expect($result)->toHaveKeys([
        'billing',
        'social_auth',
        'email_verification',
        'api_tokens',
        'user_settings',
        'notifications',
        'onboarding',
        'api_docs',
        'two_factor',
        'webhooks',
        'admin',
    ]);
});

it('resolveAll returns correct values with mixed overrides', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    config(['features.billing.enabled' => false]);
    config(['features.notifications.enabled' => false]);
    config(['features.email_verification.enabled' => true]);

    $service->setGlobalOverride('notifications', true);
    $service->setUserOverride('email_verification', $user->id, false);

    $result = $service->resolveAll($user);

    // billing: false (config default, route-dependent so override can't help)
    expect($result['billing'])->toBeFalse();
    // notifications: false (config false, route-dependent so override can't help)
    expect($result['notifications'])->toBeFalse();
    // email_verification: false (user override overrides config default of true)
    expect($result['email_verification'])->toBeFalse();
});

it('getDefinedFlags excludes config entries without enabled key', function () {
    $service = app(FeatureFlagService::class);

    // admin.pagination is a nested config, not a flag
    $flags = $service->getDefinedFlags();

    // Should not include 'pagination' as it's not a flag
    expect(array_key_exists('pagination', $flags))->toBeFalse();
    // Should include 'admin' as it has enabled key
    expect(array_key_exists('admin', $flags))->toBeTrue();
});

it('getAdminSummary returns correct shape', function () {
    $service = app(FeatureFlagService::class);

    $summary = $service->getAdminSummary();

    expect($summary)->toBeArray();
    expect(count($summary))->toBeGreaterThan(0);

    $first = $summary[0];
    expect($first)->toHaveKeys([
        'flag',
        'env_default',
        'global_override',
        'effective',
        'user_override_count',
        'is_protected',
        'is_route_dependent',
        'reason',
        'updated_at',
    ]);
});

it('getAdminSummary includes user override counts', function () {
    $service = app(FeatureFlagService::class);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $service->setUserOverride('billing', $user1->id, true);
    $service->setUserOverride('billing', $user2->id, false);

    $summary = $service->getAdminSummary();
    $billingFlag = collect($summary)->firstWhere('flag', 'billing');

    expect($billingFlag['user_override_count'])->toBe(2);
});

it('getTargetedUsers returns users with overrides for flag', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

    $service->setUserOverride('billing', $user->id, true);

    $users = $service->getTargetedUsers('billing');

    expect($users)->toHaveCount(1);
    expect($users[0])->toMatchArray([
        'user_id' => $user->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'enabled' => true,
    ]);
});

it('cache is invalidated on global override change', function () {
    $service = app(FeatureFlagService::class);

    config(['features.billing.enabled' => false]);

    // First call caches the result
    expect($service->resolve('billing'))->toBeFalse();

    // Set global override - should invalidate cache
    $service->setGlobalOverride('billing', true);

    // Should return new value (route-dependent so still false, but let's test with non-route-dependent)
    config(['features.email_verification.enabled' => false]);
    expect($service->resolve('email_verification'))->toBeFalse();

    $service->setGlobalOverride('email_verification', true);
    expect($service->resolve('email_verification'))->toBeTrue();
});

it('cache is invalidated on user override change', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    config(['features.email_verification.enabled' => false]);

    // First call caches the result
    expect($service->resolve('email_verification', $user))->toBeFalse();

    // Set user override - should invalidate user cache
    $service->setUserOverride('email_verification', $user->id, true);

    // Should return new value
    expect($service->resolve('email_verification', $user))->toBeTrue();
});

it('removeAllUserOverrides removes all user overrides for a flag', function () {
    $service = app(FeatureFlagService::class);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $service->setUserOverride('billing', $user1->id, true);
    $service->setUserOverride('billing', $user2->id, false);

    expect($service->getTargetedUsers('billing'))->toHaveCount(2);

    $service->removeAllUserOverrides('billing');

    expect($service->getTargetedUsers('billing'))->toHaveCount(0);
});

it('searchUsers returns matching users', function () {
    $service = app(FeatureFlagService::class);

    User::factory()->create(['name' => 'Alice Test', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Test', 'email' => 'bob@example.com']);
    User::factory()->create(['name' => 'Charlie', 'email' => 'charlie@other.com']);

    // Search by name
    $results = $service->searchUsers('Alice');
    expect($results)->toHaveCount(1);
    expect($results[0]['name'])->toBe('Alice Test');

    // Search by email domain
    $results = $service->searchUsers('example.com');
    expect($results)->toHaveCount(2);

    // Search by partial match
    $results = $service->searchUsers('Test');
    expect($results)->toHaveCount(2);
});

it('stores reason and changed_by when setting global override', function () {
    $service = app(FeatureFlagService::class);
    $admin = User::factory()->create();

    $service->setGlobalOverride('email_verification', true, 'Testing rollout', $admin);

    $override = FeatureFlagOverride::where('flag', 'email_verification')
        ->whereNull('user_id')
        ->first();

    expect($override)->not->toBeNull();
    expect($override->reason)->toBe('Testing rollout');
    expect($override->changed_by)->toBe($admin->id);
});

it('stores reason and changed_by when setting user override', function () {
    $service = app(FeatureFlagService::class);
    $admin = User::factory()->create();
    $targetUser = User::factory()->create();

    $service->setUserOverride('email_verification', $targetUser->id, true, 'Beta access', $admin);

    $override = FeatureFlagOverride::where('flag', 'email_verification')
        ->where('user_id', $targetUser->id)
        ->first();

    expect($override)->not->toBeNull();
    expect($override->reason)->toBe('Beta access');
    expect($override->changed_by)->toBe($admin->id);
});

it('getAdminSummary includes reason and updated_at from global override', function () {
    $service = app(FeatureFlagService::class);
    $admin = User::factory()->create();

    $service->setGlobalOverride('email_verification', true, 'Production rollout', $admin);

    $summary = $service->getAdminSummary();
    $flag = collect($summary)->firstWhere('flag', 'email_verification');

    expect($flag['reason'])->toBe('Production rollout');
    expect($flag['updated_at'])->not->toBeNull();
});
