<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
