<?php

use App\Models\User;
use App\Services\FeatureFlagService;

if (! function_exists('feature_enabled')) {
    /**
     * Check if a feature flag is enabled for the given user.
     *
     * Resolution order:
     * 1. User-specific override (if user provided)
     * 2. Global database override
     * 3. Config default from features.php
     *
     * @param  string  $flag  The feature flag key (e.g., 'billing', 'two_factor')
     * @param  User|null  $user  The user to check for (defaults to authenticated user)
     */
    function feature_enabled(string $flag, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        return app(FeatureFlagService::class)->resolve($flag, $user);
    }
}
