<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
