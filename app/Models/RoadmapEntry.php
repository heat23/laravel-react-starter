<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
