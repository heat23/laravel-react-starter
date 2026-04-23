<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomingWebhook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomingWebhook newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomingWebhook query()
 *
 * @mixin \Eloquent
 */
class IncomingWebhook extends Model
{
    protected $fillable = [
        'provider',
        'external_id',
        'event_type',
        'payload',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
