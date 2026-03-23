<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStageHistory extends Model
{
    protected $table = 'user_stage_history';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'from_stage',
        'to_stage',
        'reason',
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
}
