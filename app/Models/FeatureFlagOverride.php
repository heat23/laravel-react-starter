<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
