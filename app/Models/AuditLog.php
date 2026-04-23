<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $event
 * @property int|null $user_id
 * @property string|null $ip
 * @property string|null $user_agent
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property-read User|null $user
 *
 * @method static Builder<static>|AuditLog byEvent(string $event)
 * @method static Builder<static>|AuditLog byUser(int $userId)
 * @method static \Database\Factories\AuditLogFactory factory($count = null, $state = [])
 * @method static Builder<static>|AuditLog newModelQuery()
 * @method static Builder<static>|AuditLog newQuery()
 * @method static Builder<static>|AuditLog query()
 * @method static Builder<static>|AuditLog recent(int $days = 30)
 * @method static Builder<static>|AuditLog whereCreatedAt($value)
 * @method static Builder<static>|AuditLog whereEvent($value)
 * @method static Builder<static>|AuditLog whereId($value)
 * @method static Builder<static>|AuditLog whereIp($value)
 * @method static Builder<static>|AuditLog whereMetadata($value)
 * @method static Builder<static>|AuditLog whereUserAgent($value)
 * @method static Builder<static>|AuditLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
class AuditLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * Auth-related event prefixes that require full IP retention for abuse detection.
     * All other events may have their IPs anonymized when the feature is enabled.
     */
    private const SECURITY_EVENT_PREFIXES = [
        'auth.',
        'admin.unauthorized_access',
    ];

    /**
     * Anonymize an IP address by zeroing the last octet (IPv4) or
     * last 80 bits (IPv6). Returns null if input is null.
     */
    public static function anonymizeIp(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return $ip;
        }

        // IPv4-mapped IPv6 (::ffff:x.x.x.x) — normalize to IPv4 before masking
        if (preg_match('/^(::ffff:)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/i', $ip, $matches)) {
            $prefix = $matches[1];
            $ipv4 = $matches[2];
            if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $prefix.preg_replace('/\.\d+$/', '.0', $ipv4);
            }

            // Embedded value matched the digit pattern but is not valid IPv4
            // (e.g., ::ffff:999.0.0.1). Return masked placeholder to avoid
            // leaking the raw address through the IPv6 branch.
            return $prefix.'0.0.0.0';
        }

        // IPv4: replace last octet with 0
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip) ?? $ip;
        }

        // IPv6: expand to full form, zero out last 80 bits (last 5 groups)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $binary = inet_pton($ip);
            if ($binary === false) {
                return $ip;
            }
            $hex = bin2hex($binary);
            // Zero out last 80 bits = last 20 hex chars (5 groups × 2 bytes × 2 hex digits per byte)
            $masked = substr($hex, 0, 12).str_repeat('0', 20);
            $packed = hex2bin($masked);

            return $packed !== false ? inet_ntop($packed) : $ip;
        }

        return $ip;
    }

    /**
     * Determine if an event requires full IP retention for security/abuse detection.
     */
    public static function isSecurityEvent(string $event): bool
    {
        foreach (self::SECURITY_EVENT_PREFIXES as $prefix) {
            if (str_starts_with($event, $prefix)) {
                return true;
            }
        }

        return false;
    }

    protected $fillable = [
        'event',
        'user_id',
        'ip',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * The user who triggered this audit event.
     *
     * IMPORTANT: withTrashed() is intentional — audit logs must retain actor
     * information even after a user is soft-deleted. Removing withTrashed()
     * causes user_name/user_email to appear as null in recent_activity and
     * audit log views for any events logged before the deletion.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function toDetailArray(): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'user_name' => $this->user?->name,
            'user_email' => $this->user?->email,
            'user_id' => $this->user_id,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    public function toSummaryArray(): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'user_name' => $this->user?->name,
            'user_email' => $this->user?->email,
            'ip' => $this->ip,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
