<?php

use App\Models\FeatureFlagOverride;
use App\Models\User;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    ensureFeatureFlagOverridesTableExists();
    clearFeatureFlagOverrides();
    Cache::flush();
    // Per-process hard-dep warning dedup must be reset between tests so each
    // case observes a fresh first-time warning rather than inheriting state.
    FeatureFlagService::resetDependencyWarningCache();
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

    // Must match config/features.php exactly — using array_keys (not toHaveKeys,
    // which is a subset check) so a flag added to config without a matching
    // update here fails the test.
    expect(array_keys($result))->toEqualCanonicalizing([
        'billing',
        'social_auth',
        'email_verification',
        'api_tokens',
        'user_settings',
        'notifications',
        'onboarding',
        'two_factor',
        'webhooks',
        'admin',
        'indexnow',
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
    // notifications: true (global override enabled it, not route-dependent so works)
    expect($result['notifications'])->toBeTrue();
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
        'blocked_by_dependency',
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

it('runtime-checked flags can be enabled via DB override when env is false', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    // These flags have routes always registered, controllers gate via feature_enabled()
    // So DB overrides SHOULD work even when env is false
    $runtimeCheckedFlags = ['notifications', 'onboarding', 'two_factor', 'webhooks'];

    foreach ($runtimeCheckedFlags as $flag) {
        config(["features.{$flag}.enabled" => false]);

        // Set DB override
        $service->setGlobalOverride($flag, true);

        // Should return true because routes are always registered
        expect($service->resolve($flag))->toBeTrue(
            "Expected {$flag} to be enabled via DB override (runtime-checked flag)"
        );

        // Clean up
        $service->removeGlobalOverride($flag);
    }
});

it('route-dependent flags cannot be enabled via DB override when env is false', function () {
    $service = app(FeatureFlagService::class);

    // These flags have routes conditionally registered via if (config(...))
    // So DB overrides CANNOT work when env is false (routes don't exist)
    $routeDependentFlags = ['billing', 'social_auth', 'api_tokens', 'admin'];

    foreach ($routeDependentFlags as $flag) {
        config(["features.{$flag}.enabled" => false]);

        // Set DB override (simulating edge case)
        FeatureFlagOverride::create([
            'flag' => $flag,
            'user_id' => null,
            'enabled' => true,
        ]);

        // Should still return false because routes aren't registered
        expect($service->resolve($flag))->toBeFalse(
            "Expected {$flag} to remain disabled (route-dependent flag, env=false)"
        );

        // Clean up
        FeatureFlagOverride::where('flag', $flag)->delete();
        Cache::flush();
    }
});

/*
|--------------------------------------------------------------------------
| Hard-Dependency Enforcement (FeatureFlagService::HARD_DEPENDENCIES)
|--------------------------------------------------------------------------
| When a flag has a hard dependency and that dependency is disabled, the
| flag MUST resolve to false and a warning MUST be logged. This prevents
| the "silent broken" class of bugs where a feature's prerequisites are
| misconfigured and the feature half-works.
*/

it('resolves onboarding to false when user_settings is disabled', function () {
    $service = app(FeatureFlagService::class);

    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => false,
    ]);

    expect($service->resolve('onboarding'))->toBeFalse();
});

it('logs a warning when a hard dependency is off', function () {
    $service = app(FeatureFlagService::class);

    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => false,
    ]);

    Log::spy();

    $service->resolve('onboarding');

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message) => str_contains($message, "'onboarding'")
            && str_contains($message, "'user_settings'"));
});

it('does not log a warning when all hard dependencies are satisfied', function () {
    $service = app(FeatureFlagService::class);

    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => true,
    ]);

    Log::spy();

    $service->resolve('onboarding');

    Log::shouldNotHaveReceived('warning');
});

it('hard dependency gate overrides user override on the dependent flag', function () {
    $service = app(FeatureFlagService::class);
    $user = User::factory()->create();

    // Dependency is off
    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => false,
    ]);

    // User tries to explicitly enable onboarding — dep gate still wins
    $service->setUserOverride('onboarding', $user->id, true);

    expect($service->resolve('onboarding', $user))->toBeFalse();
});

it('does not log a dependency warning when the dependent flag is off', function () {
    $service = app(FeatureFlagService::class);

    // onboarding off; user_settings off too. Warning should NOT fire because
    // the caller wasn't trying to turn onboarding on — no broken state to flag.
    config([
        'features.onboarding.enabled' => false,
        'features.user_settings.enabled' => false,
    ]);

    Log::spy();

    expect($service->resolve('onboarding'))->toBeFalse();
    Log::shouldNotHaveReceived('warning');
});

it('honors dependency resolution through global overrides', function () {
    $service = app(FeatureFlagService::class);

    // Dependency disabled in config, but globally overridden to true
    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => false,
    ]);
    $service->setGlobalOverride('user_settings', true);

    // Dep check uses override-aware resolution, so onboarding resolves true
    expect($service->resolve('onboarding'))->toBeTrue();
});

it('de-dups hard-dependency warnings within a process', function () {
    $service = app(FeatureFlagService::class);

    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => false,
    ]);

    Log::spy();

    // resolveAll runs once per request in HandleInertiaRequests — simulating a
    // handful of requests under a misconfiguration should not flood the log.
    $service->resolve('onboarding');
    $service->resolve('onboarding');
    $service->resolve('onboarding');

    Log::shouldHaveReceived('warning')->once();
});

it('getAdminSummary applies hard-dependency gate to effective', function () {
    $service = app(FeatureFlagService::class);

    // onboarding is configured on but its hard dep user_settings is off
    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => false,
    ]);

    $summary = $service->getAdminSummary();
    $onboarding = collect($summary)->firstWhere('flag', 'onboarding');

    // Admin UI must report the same effective value that resolve() returns at runtime
    expect($onboarding['effective'])->toBeFalse();
    expect($onboarding['blocked_by_dependency'])->toBe('user_settings');
    // env_default should still reflect the raw config — the gate is an overlay
    expect($onboarding['env_default'])->toBeTrue();
});

it('getAdminSummary leaves blocked_by_dependency null when deps are satisfied', function () {
    $service = app(FeatureFlagService::class);

    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => true,
    ]);

    $summary = $service->getAdminSummary();
    $onboarding = collect($summary)->firstWhere('flag', 'onboarding');

    expect($onboarding['effective'])->toBeTrue();
    expect($onboarding['blocked_by_dependency'])->toBeNull();
});
