<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Models\FeatureFlagOverride;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeatureFlagOverrideStore
{
    public function getGlobalOverrides(): array
    {
        try {
            return Cache::remember(
                AdminCacheKey::FEATURE_FLAGS_GLOBAL->value,
                AdminCacheKey::DEFAULT_TTL,
                fn () => FeatureFlagOverride::query()
                    ->global()
                    ->pluck('enabled', 'flag')
                    ->map(fn ($v) => (bool) $v)
                    ->toArray()
            );
        } catch (QueryException) {
            return [];
        }
    }

    public function getGlobalOverride(string $flag): ?bool
    {
        return $this->getGlobalOverrides()[$flag] ?? null;
    }

    public function getUserOverrides(int $userId): array
    {
        try {
            return Cache::remember(
                AdminCacheKey::featureFlagsUser($userId),
                AdminCacheKey::DEFAULT_TTL,
                fn () => FeatureFlagOverride::query()
                    ->forUser($userId)
                    ->pluck('enabled', 'flag')
                    ->map(fn ($v) => (bool) $v)
                    ->toArray()
            );
        } catch (QueryException) {
            return [];
        }
    }

    public function getUserOverride(string $flag, int $userId): ?bool
    {
        return $this->getUserOverrides($userId)[$flag] ?? null;
    }

    public function getUserOverrideCounts(): array
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

    public function getGlobalOverrideMetadata(): array
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

    public function getTargetedUsers(string $flag): array
    {
        $rows = FeatureFlagOverride::query()
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
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[] = [
                'user_id' => $row->user_id,
                'name' => $row->name,
                'email' => $row->email,
                'enabled' => (bool) $row->enabled,
            ];
        }

        return $result;
    }

    public function upsertGlobalOverride(string $flag, bool $enabled, ?string $reason, ?int $changedBy): void
    {
        FeatureFlagOverride::updateOrCreate(
            ['flag' => $flag, 'user_id' => null],
            ['enabled' => $enabled, 'reason' => $reason, 'changed_by' => $changedBy]
        );
    }

    public function deleteGlobalOverride(string $flag): void
    {
        FeatureFlagOverride::query()->forFlag($flag)->global()->delete();
    }

    public function upsertUserOverride(string $flag, int $userId, bool $enabled, ?string $reason, ?int $changedBy): void
    {
        FeatureFlagOverride::updateOrCreate(
            ['flag' => $flag, 'user_id' => $userId],
            ['enabled' => $enabled, 'reason' => $reason, 'changed_by' => $changedBy]
        );
    }

    public function deleteUserOverride(string $flag, int $userId): void
    {
        FeatureFlagOverride::query()->forFlag($flag)->forUser($userId)->delete();
    }

    /**
     * @return array<int> affected user IDs
     */
    public function deleteAllUserOverrides(string $flag): array
    {
        return DB::transaction(function () use ($flag) {
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
    }
}
