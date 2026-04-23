<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Feedback> $feedbackSubmissions
 * @property-read int|null $feedback_submissions_count
 * @property-read int $feedback_count
 *
 * @method static Builder<static>|RoadmapEntry byStatus(string $status)
 * @method static Builder<static>|RoadmapEntry newModelQuery()
 * @method static Builder<static>|RoadmapEntry newQuery()
 * @method static Builder<static>|RoadmapEntry query()
 * @method static Builder<static>|RoadmapEntry whereCreatedAt($value)
 * @method static Builder<static>|RoadmapEntry whereDescription($value)
 * @method static Builder<static>|RoadmapEntry whereDisplayOrder($value)
 * @method static Builder<static>|RoadmapEntry whereId($value)
 * @method static Builder<static>|RoadmapEntry whereSlug($value)
 * @method static Builder<static>|RoadmapEntry whereStatus($value)
 * @method static Builder<static>|RoadmapEntry whereTitle($value)
 * @method static Builder<static>|RoadmapEntry whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class RoadmapEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'status',
        'display_order',
    ];

    public function feedbackSubmissions(): HasMany
    {
        return $this->hasMany(Feedback::class, 'roadmap_entry_id');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getFeedbackCountAttribute(): int
    {
        return $this->feedbackSubmissions()->count();
    }
}
