<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
