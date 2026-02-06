<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * User Setting Model
 *
 * Key-value store for user preferences.
 * Only used when FEATURE_USER_SETTINGS=true
 *
 * Common keys:
 * - 'theme': 'light' | 'dark' | 'system'
 * - 'timezone': IANA timezone string
 * - 'notifications_email': boolean
 */
class UserSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * Get the user that owns the setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a setting value for a user.
     */
    public static function getValue(int $userId, string $key, mixed $default = null): mixed
    {
        $cacheKey = "user_setting:{$userId}:{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($userId, $key, $default) {
            $setting = static::where('user_id', $userId)
                ->where('key', $key)
                ->first();

            if (! $setting) {
                return $default;
            }

            // Try to decode JSON values
            $value = $setting->value;
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        });
    }

    /**
     * Set a setting value for a user.
     */
    public static function setValue(int $userId, string $key, mixed $value): static
    {
        // Encode non-string values as JSON
        $storedValue = is_string($value) ? $value : json_encode($value);

        $setting = static::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $storedValue]
        );

        // Clear cache
        Cache::forget("user_setting:{$userId}:{$key}");

        return $setting;
    }

    /**
     * Delete a setting for a user.
     */
    public static function deleteSetting(int $userId, string $key): bool
    {
        Cache::forget("user_setting:{$userId}:{$key}");

        return static::where('user_id', $userId)
            ->where('key', $key)
            ->delete() > 0;
    }
}
