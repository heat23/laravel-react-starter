<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * WebhookEndpoint Model
 *
 * Note: Uses SoftDeletes as an intentional exception to the project's hard-delete default.
 * Soft deletes preserve webhook delivery history and audit trails when endpoints are removed.
 */
class WebhookEndpoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'url',
        'events',
        'secret',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'secret' => 'encrypted',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
