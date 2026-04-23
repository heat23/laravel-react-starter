<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class FeatureFlagService
{
    private const ROUTE_DEPENDENT_FLAGS = ['billing', 'social_auth', 'api_tokens', 'admin'];

    private const HARD_DEPENDENCIES = ['onboarding' => ['user_settings']];

    private static array $warnedDependencies = [];

    public function __construct(
        private readonly FeatureFlagValidator $validator,
        private readonly FeatureFlagOverrideStore $store,
        private readonly CacheInvalidationManager $cacheManager,
    ) {}

    public function resolve(string $flag, ?User $user = null): bool
    {
        $this->validator->validateFlag($flag);
        $raw = $this->resolveRaw($flag, $user);

        if (! $raw) {
            return false;
        }

        if (isset(self::HARD_DEPENDENCIES[$flag])) {
            foreach (self::HARD_DEPENDENCIES[$flag] as $dep) {
                if (! $this->resolveRaw($dep, $user)) {
                    $warnKey = $flag.':'.$dep;

                    if (! isset(self::$warnedDependencies[$warnKey])) {
                        self::$warnedDependencies[$warnKey] = true;
                        Log::warning("Feature flag '{$flag}' resolved to false because hard dependency '{$dep}' is disabled");
                    }

                    return false;
                }
            }
        }

        return true;
    }

    public static function resetDependencyWarningCache(): void
    {
        self::$warnedDependencies = [];
    }

    public function resolveAll(?User $user = null): array
    {
        $result = [];
        foreach (array_keys($this->getDefinedFlags()) as $flag) {
            $result[$flag] = $this->resolve($flag, $user);
        }

        return $result;
    }

    public function getDefinedFlags(): array
    {
        return $this->validator->getDefinedFlags();
    }

    public function getAdminSummary(): array
    {
        $flags = $this->getDefinedFlags();
        $globalOverrides = $this->store->getGlobalOverrides();
        $userOverrideCounts = $this->store->getUserOverrideCounts();
        $globalOverrideMetadata = $this->store->getGlobalOverrideMetadata();

        $preDepEffective = [];
        foreach ($flags as $flag => $envDefault) {
            $preDepEffective[$flag] = (in_array($flag, self::ROUTE_DEPENDENT_FLAGS, true) && ! $envDefault)
                ? false
                : ($globalOverrides[$flag] ?? $envDefault);
        }
        $result = [];
        foreach ($flags as $flag => $envDefault) {
            $effective = $preDepEffective[$flag];
            $blockedBy = null;

            if ($effective && isset(self::HARD_DEPENDENCIES[$flag])) {
                foreach (self::HARD_DEPENDENCIES[$flag] as $dep) {
                    if (! ($preDepEffective[$dep] ?? false)) {
                        $blockedBy = $dep;
                        $effective = false;
                        break;
                    }
                }
            }

            $result[] = [
                'flag' => $flag,
                'env_default' => $envDefault,
                'global_override' => $globalOverrides[$flag] ?? null,
                'effective' => $effective,
                'user_override_count' => $userOverrideCounts[$flag] ?? 0,
                'is_protected' => $this->validator->isProtected($flag),
                'is_route_dependent' => in_array($flag, self::ROUTE_DEPENDENT_FLAGS, true),
                'blocked_by_dependency' => $blockedBy,
                'reason' => $globalOverrideMetadata[$flag]['reason'] ?? null,
                'updated_at' => $globalOverrideMetadata[$flag]['updated_at'] ?? null,
            ];
        }

        return $result;
    }

    public function getTargetedUsers(string $flag): array
    {
        $this->validator->validateFlag($flag);

        return $this->store->getTargetedUsers($flag);
    }

    public function setGlobalOverride(string $flag, bool $enabled, ?string $reason = null, ?User $adminUser = null): void
    {
        $this->validator->validateFlag($flag);
        $this->validator->validateNotProtected($flag);
        $this->store->upsertGlobalOverride($flag, $enabled, $reason, $adminUser?->id);
        $this->cacheManager->invalidateFeatureFlagsGlobal();
    }

    public function removeGlobalOverride(string $flag): void
    {
        $this->validator->validateFlag($flag);
        $this->store->deleteGlobalOverride($flag);
        $this->cacheManager->invalidateFeatureFlagsGlobal();
    }

    public function setUserOverride(string $flag, int $userId, bool $enabled, ?string $reason = null, ?User $adminUser = null): void
    {
        $this->validator->validateFlag($flag);
        $this->validator->validateNotProtected($flag);
        $this->store->upsertUserOverride($flag, $userId, $enabled, $reason, $adminUser?->id);
        $this->cacheManager->invalidateUser($userId);
    }

    public function removeUserOverride(string $flag, int $userId): void
    {
        $this->validator->validateFlag($flag);
        $this->store->deleteUserOverride($flag, $userId);
        $this->cacheManager->invalidateUser($userId);
    }

    public function removeAllUserOverrides(string $flag): void
    {
        $this->validator->validateFlag($flag);

        foreach ($this->store->deleteAllUserOverrides($flag) as $userId) {
            $this->cacheManager->invalidateUser($userId);
        }
    }

    public function searchUsers(string $query, int $limit = 10): array
    {
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);

        return User::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$escaped}%")->orWhere('email', 'like', "%{$escaped}%"))
            ->limit($limit)
            ->select(['id', 'name', 'email'])
            ->get()
            ->toArray();
    }

    public function getExperimentVariant(string $experimentKey, User $user, array $variants = ['control', 'treatment']): string
    {
        if (empty($variants)) {
            return 'control';
        }
        $hash = abs(crc32($user->id.$experimentKey));

        return $variants[$hash % count($variants)];
    }

    private function resolveRaw(string $flag, ?User $user = null): bool
    {
        $configDefault = (bool) config("features.{$flag}.enabled", false);

        if (in_array($flag, self::ROUTE_DEPENDENT_FLAGS, true) && ! $configDefault) {
            return false;
        }

        if ($user !== null && ($userOverride = $this->store->getUserOverride($flag, $user->id)) !== null) {
            return $userOverride;
        }

        if (($globalOverride = $this->store->getGlobalOverride($flag)) !== null) {
            return $globalOverride;
        }

        return $configDefault;
    }
}
