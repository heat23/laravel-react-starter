<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Laragear\TwoFactor\TwoFactorAuthentication;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * Implements MustVerifyEmail conditionally based on feature flag.
 * To disable email verification, remove "implements MustVerifyEmail"
 * or check config('features.email_verification.enabled').
 */
class User extends Authenticatable implements MustVerifyEmail, TwoFactorAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasApiTokens, HasFactory, Notifiable, SoftDeletes, TwoFactorAuthentication;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login_at',
        'signup_source',
        'trial_ends_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if user has a password set.
     */
    public function hasPassword(): bool
    {
        return ! empty($this->password);
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get the social accounts for the user.
     * (Only used when social_auth feature is enabled)
     */
    public function socialAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the webhook endpoints for the user.
     * (Only used when webhooks feature is enabled)
     */
    public function webhookEndpoints(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WebhookEndpoint::class);
    }

    /**
     * Get the settings for the user.
     * (Only used when user_settings feature is enabled)
     */
    public function settings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    /**
     * Get a setting value for this user.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        if (! class_exists(UserSetting::class)) {
            return $default;
        }

        return UserSetting::getValue($this->id, $key, $default);
    }

    /**
     * Set a setting value for this user.
     */
    public function setSetting(string $key, mixed $value): mixed
    {
        if (! class_exists(UserSetting::class)) {
            return null;
        }

        return UserSetting::setValue($this->id, $key, $value);
    }
}
