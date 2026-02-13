<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Models\FeatureFlagOverride;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class FeatureFlagService
{
    /**
     * Flags that cannot be overridden via database (protected at code level).
     */
    private const PROTECTED_FLAGS = ['admin'];

    /**
     * Flags that depend on routes being registered at boot time.
     * If env=false, DB overrides cannot enable them (routes won't exist).
     */
    private const ROUTE_DEPENDENT_FLAGS = [
        'billing',
        'social_auth',
        'webhooks',
        'api_tokens',
        'notifications',
        'onboarding',
        'api_docs',
        'two_factor',
        'admin',
    ];

    /**
     * Resolve a single feature flag for a given user.
     *
     * Resolution order:
     * 1. User-specific override (if user provided and override exists)
     * 2. Global override (if exists)
     * 3. Config default
     *
     * Special case: Route-dependent flags with env=false return false
     * regardless of DB overrides (routes aren't registered).
     */
    public function resolve(string $flag, ?User $user = null): bool
    {
        $this->validateFlag($flag);

        $configDefault = $this->getConfigDefault($flag);

        // Route-dependent flags: if env is false, DB overrides can't help
        if (in_array($flag, self::ROUTE_DEPENDENT_FLAGS, true) && ! $configDefault) {
            return false;
        }

        // Check user-specific override first
        if ($user !== null) {
            $userOverride = $this->getUserOverride($flag, $user->id);
            if ($userOverride !== null) {
                return $userOverride;
            }
        }

        // Check global override
        $globalOverride = $this->getGlobalOverride($flag);
        if ($globalOverride !== null) {
            return $globalOverride;
        }

        // Fall back to config default
        return $configDefault;
    }

    /**
     * Resolve all defined feature flags for a given user.
     *
     * @return array<string, bool>
     */
    public function resolveAll(?User $user = null): array
    {
        $flags = $this->getDefinedFlags();
        $result = [];

        foreach (array_keys($flags) as $flag) {
            $result[$flag] = $this->resolve($flag, $user);
        }

        return $result;
    }

    /**
     * Get all defined feature flags from config.
     * Only returns entries that have an 'enabled' key (excludes nested config like pagination).
     *
     * @return array<string, bool>
     */
    public function getDefinedFlags(): array
    {
        $config = config('features', []);
        $flags = [];

        foreach ($config as $key => $value) {
            if (is_array($value) && array_key_exists('enabled', $value)) {
                $flags[$key] = (bool) $value['enabled'];
            }
        }

        return $flags;
    }

    /**
     * Get admin summary data for all flags.
     *
     * @return array<int, array{
     *     flag: string,
     *     env_default: bool,
     *     global_override: bool|null,
     *     effective: bool,
     *     user_override_count: int,
     *     is_protected: bool,
     *     is_route_dependent: bool,
     *     reason: string|null,
     *     updated_at: string|null
     * }>
     */
    public function getAdminSummary(): array
    {
        $flags = $this->getDefinedFlags();
        $globalOverrides = $this->getGlobalOverrides();
        $userOverrideCounts = $this->getUserOverrideCounts();
        $globalOverrideMetadata = $this->getGlobalOverrideMetadata();

        $result = [];

        foreach ($flags as $flag => $envDefault) {
            $globalOverride = $globalOverrides[$flag] ?? null;
            $isRouteDependentWithEnvFalse = in_array($flag, self::ROUTE_DEPENDENT_FLAGS, true) && ! $envDefault;

            // Effective value: user override checked at request time, so for admin summary
            // we show global-level effective (without user context)
            $effective = $globalOverride ?? $envDefault;
            if ($isRouteDependentWithEnvFalse) {
                $effective = false;
            }

            $metadata = $globalOverrideMetadata[$flag] ?? null;

            $result[] = [
                'flag' => $flag,
                'env_default' => $envDefault,
                'global_override' => $globalOverride,
                'effective' => $effective,
                'user_override_count' => $userOverrideCounts[$flag] ?? 0,
                'is_protected' => in_array($flag, self::PROTECTED_FLAGS, true),
                'is_route_dependent' => in_array($flag, self::ROUTE_DEPENDENT_FLAGS, true),
                'reason' => $metadata['reason'] ?? null,
                'updated_at' => $metadata['updated_at'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Get users with overrides for a specific flag.
     * Excludes soft-deleted users.
     *
     * @return array<int, array{user_id: int, name: string, email: string, enabled: bool}>
     */
    public function getTargetedUsers(string $flag): array
    {
        $this->validateFlag($flag);

        return FeatureFlagOverride::query()
            ->forFlag($flag)
            ->whereNotNull('user_id')
            ->join('users', 'feature_flag_overrides.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->select([
                'feature_flag_overrides.user_id',
                'users.name',
                'users.email',
                'feature_flag_overrides.enabled',
            ])
            ->get()
            ->map(fn ($row) => [
                'user_id' => $row->user_id,
                'name' => $row->name,
                'email' => $row->email,
                'enabled' => (bool) $row->enabled,
            ])
            ->toArray();
    }

    /**
     * Set or update a global override.
     */
    public function setGlobalOverride(string $flag, bool $enabled, ?string $reason = null, ?User $adminUser = null): void
    {
        $this->validateFlag($flag);
        $this->validateNotProtected($flag);

        FeatureFlagOverride::updateOrCreate(
            ['flag' => $flag, 'user_id' => null],
            [
                'enabled' => $enabled,
                'reason' => $reason,
                'changed_by' => $adminUser?->id,
            ]
        );

        $this->invalidateGlobalCache();
    }

    /**
     * Remove a global override (reverts to config default).
     */
    public function removeGlobalOverride(string $flag): void
    {
        $this->validateFlag($flag);

        FeatureFlagOverride::query()
            ->forFlag($flag)
            ->global()
            ->delete();

        $this->invalidateGlobalCache();
    }

    /**
     * Set or update a user-specific override.
     */
    public function setUserOverride(string $flag, int $userId, bool $enabled, ?string $reason = null, ?User $adminUser = null): void
    {
        $this->validateFlag($flag);
        $this->validateNotProtected($flag);

        FeatureFlagOverride::updateOrCreate(
            ['flag' => $flag, 'user_id' => $userId],
            [
                'enabled' => $enabled,
                'reason' => $reason,
                'changed_by' => $adminUser?->id,
            ]
        );

        $this->invalidateUserCache($userId);
    }

    /**
     * Remove a user-specific override.
     */
    public function removeUserOverride(string $flag, int $userId): void
    {
        $this->validateFlag($flag);

        FeatureFlagOverride::query()
            ->forFlag($flag)
            ->forUser($userId)
            ->delete();

        $this->invalidateUserCache($userId);
    }

    /**
     * Remove all user overrides for a flag.
     */
    public function removeAllUserOverrides(string $flag): void
    {
        $this->validateFlag($flag);

        // Use DB transaction to prevent race condition between pluck and delete
        $userIds = DB::transaction(function () use ($flag) {
            $userIds = FeatureFlagOverride::query()
                ->forFlag($flag)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();

            FeatureFlagOverride::query()
                ->forFlag($flag)
                ->whereNotNull('user_id')
                ->delete();

            return $userIds;
        });

        // Invalidate cache for each affected user
        foreach ($userIds as $userId) {
            $this->invalidateUserCache($userId);
        }
    }

    /**
     * Search users by name or email for targeting.
     *
     * @return array<int, array{id: int, name: string, email: string}>
     */
    public function searchUsers(string $query, int $limit = 10): array
    {
        // Escape SQL LIKE wildcards to prevent injection
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);

        return User::query()
            ->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('email', 'like', "%{$escaped}%");
            })
            ->limit($limit)
            ->select(['id', 'name', 'email'])
            ->get()
            ->toArray();
    }

    /**
     * Get the config default for a flag.
     */
    private function getConfigDefault(string $flag): bool
    {
        return (bool) config("features.{$flag}.enabled", false);
    }

    /**
     * Get all global overrides from cache or database.
     *
     * @return array<string, bool>
     */
    private function getGlobalOverrides(): array
    {
        try {
            return Cache::remember(
                AdminCacheKey::FEATURE_FLAGS_GLOBAL->value,
                AdminCacheKey::DEFAULT_TTL,
                function () {
                    return FeatureFlagOverride::query()
                        ->global()
                        ->pluck('enabled', 'flag')
                        ->map(fn ($v) => (bool) $v)
                        ->toArray();
                }
            );
        } catch (QueryException) {
            // Table doesn't exist yet (fresh install)
            return [];
        }
    }

    /**
     * Get a single global override value.
     */
    private function getGlobalOverride(string $flag): ?bool
    {
        $overrides = $this->getGlobalOverrides();

        return $overrides[$flag] ?? null;
    }

    /**
     * Get user-specific overrides for a user.
     *
     * @return array<string, bool>
     */
    private function getUserOverrides(int $userId): array
    {
        try {
            return Cache::remember(
                AdminCacheKey::featureFlagsUser($userId),
                AdminCacheKey::DEFAULT_TTL,
                function () use ($userId) {
                    return FeatureFlagOverride::query()
                        ->forUser($userId)
                        ->pluck('enabled', 'flag')
                        ->map(fn ($v) => (bool) $v)
                        ->toArray();
                }
            );
        } catch (QueryException) {
            return [];
        }
    }

    /**
     * Get a single user override value.
     */
    private function getUserOverride(string $flag, int $userId): ?bool
    {
        $overrides = $this->getUserOverrides($userId);

        return $overrides[$flag] ?? null;
    }

    /**
     * Get count of user overrides per flag.
     *
     * @return array<string, int>
     */
    private function getUserOverrideCounts(): array
    {
        try {
            return FeatureFlagOverride::query()
                ->whereNotNull('user_id')
                ->selectRaw('flag, COUNT(*) as count')
                ->groupBy('flag')
                ->pluck('count', 'flag')
                ->toArray();
        } catch (QueryException) {
            return [];
        }
    }

    /**
     * Get metadata for global overrides (reason, updated_at).
     *
     * @return array<string, array{reason: string|null, updated_at: string|null}>
     */
    private function getGlobalOverrideMetadata(): array
    {
        try {
            return FeatureFlagOverride::query()
                ->global()
                ->get(['flag', 'reason', 'updated_at'])
                ->keyBy('flag')
                ->map(fn ($record) => [
                    'reason' => $record->reason,
                    'updated_at' => $record->updated_at?->toISOString(),
                ])
                ->toArray();
        } catch (QueryException) {
            return [];
        }
    }

    /**
     * Validate that a flag name exists in config.
     *
     * @throws InvalidArgumentException
     */
    private function validateFlag(string $flag): void
    {
        $definedFlags = $this->getDefinedFlags();

        if (! array_key_exists($flag, $definedFlags)) {
            throw new InvalidArgumentException("Unknown feature flag: {$flag}");
        }
    }

    /**
     * Validate that a flag is not protected.
     *
     * @throws RuntimeException
     */
    private function validateNotProtected(string $flag): void
    {
        if (in_array($flag, self::PROTECTED_FLAGS, true)) {
            throw new RuntimeException("Cannot override protected flag: {$flag}");
        }
    }

    /**
     * Invalidate global overrides cache.
     */
    private function invalidateGlobalCache(): void
    {
        Cache::forget(AdminCacheKey::FEATURE_FLAGS_GLOBAL->value);
    }

    /**
     * Invalidate user-specific overrides cache.
     */
    private function invalidateUserCache(int $userId): void
    {
        Cache::forget(AdminCacheKey::featureFlagsUser($userId));
    }
}
