<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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
    /** @use HasFactory<UserFactory> */
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
        'last_active_at',
        'signup_source',
        'trial_ends_at',
        'lifecycle_stage',
        'acquisition_channel',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'health_score',
        'engagement_score',
        'lead_score',
        'marketing_opt_out',
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
            'last_active_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'super_admin' => 'boolean',
            'lifecycle_stage' => 'string',
            'health_score' => 'integer',
            'engagement_score' => 'integer',
            'lead_score' => 'integer',
            'marketing_opt_out' => 'boolean',
            'lead_qualified_at' => 'datetime',
            'scores_computed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function isSuperAdmin(): bool
    {
        return $this->super_admin === true;
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
     * Check if the user has been inactive for the given number of days.
     */
    public function isInactive(int $days): bool
    {
        $lastActivity = $this->last_active_at ?? $this->last_login_at ?? $this->created_at;

        return $lastActivity->lt(now()->subDays($days));
    }

    /**
     * Get the stage history entries for the user.
     */
    public function stageHistory(): HasMany
    {
        return $this->hasMany(UserStageHistory::class);
    }

    /**
     * Get the social accounts for the user.
     * (Only used when social_auth feature is enabled)
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the webhook endpoints for the user.
     * (Only used when webhooks feature is enabled)
     */
    public function webhookEndpoints(): HasMany
    {
        return $this->hasMany(WebhookEndpoint::class);
    }

    /**
     * Get the settings for the user.
     * (Only used when user_settings feature is enabled)
     */
    public function settings(): HasMany
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

    /**
     * Permanently delete the user and all associated personal data.
     *
     * FK-cascaded tables (user_settings, social_accounts, webhook_endpoints,
     * feature_flag_overrides) are handled automatically. Morph/polymorphic
     * relations (tokens, 2FA, notifications) require manual cleanup.
     * Audit logs use nullOnDelete to preserve the trail without PII.
     */
    public function purgePersonalData(): void
    {
        DB::transaction(function () {
            // Scrub PII from audit logs atomically with deletion (GDPR right to erasure).
            // If forceDelete() fails, the transaction rolls back and no PII is lost.
            AuditLog::where('user_id', $this->id)->update([
                'ip' => null,
                'user_agent' => null,
            ]);

            // Morph relations without FK constraints
            $this->tokens()->delete();
            $this->twoFactorAuth()->delete();
            $this->notifications()->delete();

            // Force delete bypasses SoftDeletes and triggers FK cascades
            $this->forceDelete();
        });
    }
}
