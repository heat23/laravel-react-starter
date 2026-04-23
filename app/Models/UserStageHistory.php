<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $from_stage
 * @property string $to_stage
 * @property string $reason
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon $created_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory whereFromStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory whereToStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStageHistory whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
