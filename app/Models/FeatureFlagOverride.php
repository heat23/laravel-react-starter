<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $flag
 * @property int|null $user_id
 * @property bool $enabled
 * @property string|null $reason
 * @property int|null $changed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $changedByUser
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride forFlag(string $flag)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride global()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagOverride whereUserId($value)
 *
 * @mixin \Eloquent
 */
class FeatureFlagOverride extends Model
{
    protected $fillable = [
        'flag',
        'user_id',
        'enabled',
        'reason',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    /**
     * Get the user this override applies to (null for global overrides).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin user who made this change.
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope to global overrides (user_id = null).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope to user-specific overrides.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to a specific flag.
     */
    public function scopeForFlag($query, string $flag)
    {
        return $query->where('flag', $flag);
    }
}
