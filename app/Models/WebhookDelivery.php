<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read WebhookEndpoint|null $endpoint
 *
 * @method static \Database\Factories\WebhookDeliveryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookDelivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookDelivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookDelivery query()
 *
 * @mixin \Eloquent
 */
class WebhookDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_endpoint_id',
        'uuid',
        'event_type',
        'payload',
        'status',
        'response_code',
        'response_body',
        'attempts',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'delivered_at' => 'datetime',
        ];
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
