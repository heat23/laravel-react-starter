<?php

use App\Models\User;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    ensureFeatureFlagOverridesTableExists();
    clearFeatureFlagOverrides();
    Cache::flush();
});

/*
|--------------------------------------------------------------------------
| Hard Dependency: onboarding → user_settings
|--------------------------------------------------------------------------
| CLAUDE.md documents: "onboarding → requires user_settings (stores completion
| timestamp in user_settings table)". When user_settings is disabled, the
| EnsureOnboardingCompleted middleware MUST skip the onboarding check.
*/

it('skips onboarding redirect when user_settings is disabled', function () {
    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => false,
    ]);

    $user = User::factory()->create();

    // User has NOT completed onboarding, but user_settings is off
    // Middleware should skip the check and let the request through
    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

it('redirects to onboarding when user_settings is enabled and onboarding incomplete', function () {
    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => true,
    ]);

    // onboardingIncomplete() removes the onboarding_completed setting
    $user = User::factory()->onboardingIncomplete()->create();

    // Flush cache so stale setting values don't interfere
    Cache::forget("user_setting:{$user->id}:onboarding_completed");

    // User has NOT completed onboarding — middleware should redirect
    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('onboarding'));
});

it('allows access when onboarding is completed with user_settings enabled', function () {
    config([
        'features.onboarding.enabled' => true,
        'features.user_settings.enabled' => true,
    ]);

    $user = User::factory()->create();
    $user->setSetting('onboarding_completed', now()->toISOString());

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| Route-Dependent Flags: Hard Floor Enforcement
|--------------------------------------------------------------------------
| All ROUTE_DEPENDENT_FLAGS must return false when env=false, regardless
| of database overrides. This is because routes aren't registered at boot.
*/

it('enforces hard floor for all route-dependent flags', function (string $flag) {
    config(["features.{$flag}.enabled" => false]);
    $user = User::factory()->create();
    $service = app(FeatureFlagService::class);

    // Set global override to true
    $service->setGlobalOverride($flag, true);

    $result = $service->resolve($flag, $user);

    expect($result)->toBeFalse(
        "Route-dependent flag '{$flag}' with env=false must not be overrideable"
    );
})->with([
    'billing',
    'social_auth',
    'api_tokens',
    'api_docs',
]);

/*
|--------------------------------------------------------------------------
| Protected Flags: Admin Cannot Be Overridden
|--------------------------------------------------------------------------
| Protected flags throw RuntimeException on override attempts.
*/

it('throws RuntimeException when setting global override on protected flag', function () {
    $service = app(FeatureFlagService::class);

    expect(fn () => $service->setGlobalOverride('admin', true))
        ->toThrow(RuntimeException::class, 'Cannot override protected flag: admin');
});

it('throws RuntimeException when setting user override on protected flag', function () {
    $user = User::factory()->create();
    $service = app(FeatureFlagService::class);

    expect(fn () => $service->setUserOverride('admin', $user->id, true))
        ->toThrow(RuntimeException::class, 'Cannot override protected flag: admin');
});

/*
|--------------------------------------------------------------------------
| Config Default Fallback
|--------------------------------------------------------------------------
| When no overrides exist, flags resolve to their config default.
*/

it('resolves to config default when no overrides exist', function () {
    config([
        'features.notifications.enabled' => false,
        'features.email_verification.enabled' => true,
    ]);

    $user = User::factory()->create();
    $service = app(FeatureFlagService::class);

    expect($service->resolve('notifications', $user))->toBeFalse();
    expect($service->resolve('email_verification', $user))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Override Precedence Chain
|--------------------------------------------------------------------------
| Resolution order: user override > global override > config default
*/

it('follows full precedence chain: user > global > config', function () {
    config(['features.notifications.enabled' => false]);
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $service = app(FeatureFlagService::class);

    // Step 1: Config default is false
    expect($service->resolve('notifications', $user))->toBeFalse();

    // Step 2: Global override to true — affects all users
    $service->setGlobalOverride('notifications', true);
    expect($service->resolve('notifications', $user))->toBeTrue();
    expect($service->resolve('notifications', $otherUser))->toBeTrue();

    // Step 3: User override to false — overrides global for specific user only
    $service->setUserOverride('notifications', $user->id, false);
    expect($service->resolve('notifications', $user))->toBeFalse();
    expect($service->resolve('notifications', $otherUser))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Soft Dependency: feature_enabled() with disabled feature
|--------------------------------------------------------------------------
| Controllers that use feature_enabled() + abort_unless() should return 404
| when the feature is disabled at runtime.
*/

it('returns 404 for notification routes when notifications disabled', function () {
    config(['features.notifications.enabled' => false]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/notifications');

    $response->assertStatus(404);
});

/*
|--------------------------------------------------------------------------
| Onboarding Feature Disabled: No Redirect
|--------------------------------------------------------------------------
| When onboarding feature is disabled, the middleware should not interfere.
*/

it('does not redirect to onboarding when onboarding feature is disabled', function () {
    config([
        'features.onboarding.enabled' => false,
        'features.user_settings.enabled' => true,
    ]);

    $user = User::factory()->create();
    // No onboarding_completed setting

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| Invalid Flag Name Handling
|--------------------------------------------------------------------------
*/

it('throws InvalidArgumentException for unknown flag name', function () {
    $service = app(FeatureFlagService::class);

    expect(fn () => $service->resolve('nonexistent_flag'))
        ->toThrow(InvalidArgumentException::class, 'Unknown feature flag: nonexistent_flag');
});

/*
|--------------------------------------------------------------------------
| resolveAll Returns All Defined Flags
|--------------------------------------------------------------------------
*/

it('resolveAll returns a boolean for every defined flag', function () {
    $user = User::factory()->create();
    $service = app(FeatureFlagService::class);

    $result = $service->resolveAll($user);
    $defined = $service->getDefinedFlags();

    expect(array_keys($result))->toEqual(array_keys($defined));
    foreach ($result as $value) {
        expect($value)->toBeBool();
    }
});

/*
|--------------------------------------------------------------------------
| Cache Invalidation on Override Changes
|--------------------------------------------------------------------------
*/

it('invalidates cache when global override changes', function () {
    config(['features.notifications.enabled' => false]);
    $user = User::factory()->create();
    $service = app(FeatureFlagService::class);

    // First resolve caches the default
    expect($service->resolve('notifications', $user))->toBeFalse();

    // Set override — should invalidate cache
    $service->setGlobalOverride('notifications', true);

    // Should now resolve to true (cache was invalidated)
    expect($service->resolve('notifications', $user))->toBeTrue();
});

it('invalidates cache when user override changes', function () {
    config(['features.notifications.enabled' => false]);
    $user = User::factory()->create();
    $service = app(FeatureFlagService::class);

    // Set global override first
    $service->setGlobalOverride('notifications', true);
    expect($service->resolve('notifications', $user))->toBeTrue();

    // Set user override to false — should invalidate user cache
    $service->setUserOverride('notifications', $user->id, false);

    // Should now resolve to false for this user
    expect($service->resolve('notifications', $user))->toBeFalse();
});

it('invalidates cache when override is removed', function () {
    config(['features.notifications.enabled' => false]);
    $user = User::factory()->create();
    $service = app(FeatureFlagService::class);

    // Enable via global override
    $service->setGlobalOverride('notifications', true);
    expect($service->resolve('notifications', $user))->toBeTrue();

    // Remove override — should revert to config default
    $service->removeGlobalOverride('notifications');
    expect($service->resolve('notifications', $user))->toBeFalse();
});
