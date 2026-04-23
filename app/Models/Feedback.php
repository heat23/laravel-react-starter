<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $type
 * @property string $message
 * @property string $status
 * @property string $priority
 * @property string|null $admin_notes
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $roadmap_entry_id
 * @property-read RoadmapEntry|null $roadmapEntry
 * @property-read User|null $user
 *
 * @method static Builder<static>|Feedback byStatus(string $status)
 * @method static Builder<static>|Feedback byType(string $type)
 * @method static \Database\Factories\FeedbackFactory factory($count = null, $state = [])
 * @method static Builder<static>|Feedback newModelQuery()
 * @method static Builder<static>|Feedback newQuery()
 * @method static Builder<static>|Feedback query()
 * @method static Builder<static>|Feedback whereAdminNotes($value)
 * @method static Builder<static>|Feedback whereCreatedAt($value)
 * @method static Builder<static>|Feedback whereId($value)
 * @method static Builder<static>|Feedback whereMessage($value)
 * @method static Builder<static>|Feedback wherePriority($value)
 * @method static Builder<static>|Feedback whereResolvedAt($value)
 * @method static Builder<static>|Feedback whereRoadmapEntryId($value)
 * @method static Builder<static>|Feedback whereStatus($value)
 * @method static Builder<static>|Feedback whereType($value)
 * @method static Builder<static>|Feedback whereUpdatedAt($value)
 * @method static Builder<static>|Feedback whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback_submissions';

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'status',
        'priority',
        'admin_notes',
        'roadmap_entry_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roadmapEntry(): BelongsTo
    {
        return $this->belongsTo(RoadmapEntry::class);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
