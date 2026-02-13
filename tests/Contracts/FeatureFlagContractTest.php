<?php

namespace Tests\Contracts;

use App\Models\User;
use App\Services\FeatureFlagService;
use Tests\TestCase;

/**
 * Contract Test: Feature Flag System
 *
 * These tests define the IMMUTABLE contract for feature flag behavior.
 * If any of these tests fail, it indicates a breaking change that MUST be fixed.
 *
 * DO NOT MODIFY THESE TESTS unless you're intentionally changing the contract.
 * DO NOT skip or mark as risky.
 */
class FeatureFlagContractTest extends TestCase
{
    /**
     * CONTRACT: Route-dependent flags with env=false CANNOT be overridden
     */
    public function test_route_dependent_flags_respect_env_hard_floor(): void
    {
        config(['features.billing.enabled' => false]); // Routes NOT registered
        $user = User::factory()->create();
        $service = app(FeatureFlagService::class);

        // Set global override to TRUE
        $service->setGlobalOverride('billing', true);

        // Despite override, should still be FALSE because routes aren't registered
        $result = $service->resolveAll($user);

        $this->assertFalse(
            $result['billing'],
            'Route-dependent flag with env=false MUST NOT be overrideable via database. This is a security contract.'
        );
    }

    /**
     * CONTRACT: User overrides take precedence over global overrides
     */
    public function test_user_override_precedence(): void
    {
        config(['features.api_tokens.enabled' => false]);
        $user = User::factory()->create();
        $service = app(FeatureFlagService::class);

        $service->setGlobalOverride('api_tokens', true);  // Global: true
        $service->setUserOverride('api_tokens', $user->id, false);  // User: false

        $result = $service->resolveAll($user);

        $this->assertFalse(
            $result['api_tokens'],
            'User-specific override MUST take precedence over global override'
        );
    }

    /**
     * CONTRACT: Protected flags cannot be overridden at all
     */
    public function test_admin_flag_cannot_be_overridden(): void
    {
        config(['features.admin.enabled' => false]);
        $user = User::factory()->create();
        $service = app(FeatureFlagService::class);

        // Attempt to override (should fail silently or throw exception)
        $service->setGlobalOverride('admin', true);
        $service->setUserOverride('admin', $user->id, true);

        $result = $service->resolveAll($user);

        $this->assertFalse(
            $result['admin'],
            'Admin flag MUST NOT be overrideable for security reasons'
        );
    }

    /**
     * CONTRACT: Non-route-dependent flags CAN be overridden regardless of env
     */
    public function test_non_route_dependent_flags_can_override(): void
    {
        config(['features.email_verification.enabled' => false]);
        $user = User::factory()->create();
        $service = app(FeatureFlagService::class);

        $service->setUserOverride('email_verification', $user->id, true);

        $result = $service->resolveAll($user);

        $this->assertTrue(
            $result['email_verification'],
            'Non-route-dependent flags MUST be overrideable via database'
        );
    }

    /**
     * CONTRACT: Admin flag routes are NOT registered when disabled
     */
    public function test_admin_routes_not_registered_when_disabled(): void
    {
        config(['features.admin.enabled' => false]);

        // Re-register routes with new config
        app()->make(\Illuminate\Routing\Router::class)->getRoutes()->refreshNameLookups();

        // Admin routes should not exist
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get('/admin');

        $this->assertEquals(
            404,
            $response->status(),
            'Admin routes MUST NOT be registered when admin.enabled=false'
        );
    }
}
