<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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
 *
 * @property array $events
 * @property-read Collection<int, WebhookDelivery> $deliveries
 * @property-read int|null $deliveries_count
 * @property-read User|null $user
 *
 * @method static \Database\Factories\WebhookEndpointFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEndpoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEndpoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEndpoint onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEndpoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEndpoint withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEndpoint withoutTrashed()
 *
 * @mixin \Eloquent
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
