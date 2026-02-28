<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
