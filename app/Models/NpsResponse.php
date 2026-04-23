<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $score
 * @property string|null $comment
 * @property string $survey_trigger
 * @property Carbon $created_at
 * @property-read string $category
 * @property-read User|null $user
 *
 * @method static \Database\Factories\NpsResponseFactory factory($count = null, $state = [])
 * @method static Builder<static>|NpsResponse latestPerUser()
 * @method static Builder<static>|NpsResponse newModelQuery()
 * @method static Builder<static>|NpsResponse newQuery()
 * @method static Builder<static>|NpsResponse query()
 * @method static Builder<static>|NpsResponse whereComment($value)
 * @method static Builder<static>|NpsResponse whereCreatedAt($value)
 * @method static Builder<static>|NpsResponse whereId($value)
 * @method static Builder<static>|NpsResponse whereScore($value)
 * @method static Builder<static>|NpsResponse whereSurveyTrigger($value)
 * @method static Builder<static>|NpsResponse whereUserId($value)
 *
 * @mixin \Eloquent
 */
class NpsResponse extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'score',
        'comment',
        'survey_trigger',
        'created_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * NPS category: promoter (9-10), passive (7-8), detractor (0-6).
     */
    public function getCategoryAttribute(): string
    {
        return match (true) {
            $this->score >= 9 => 'promoter',
            $this->score >= 7 => 'passive',
            default => 'detractor',
        };
    }

    public function scopeLatestPerUser(Builder $query): Builder
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('nps_responses')
                ->groupBy('user_id');
        });
    }
}
