<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $message
 * @property string $status
 * @property Carbon|null $replied_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|ContactSubmission byStatus(string $status)
 * @method static \Database\Factories\ContactSubmissionFactory factory($count = null, $state = [])
 * @method static Builder<static>|ContactSubmission newModelQuery()
 * @method static Builder<static>|ContactSubmission newQuery()
 * @method static Builder<static>|ContactSubmission query()
 * @method static Builder<static>|ContactSubmission whereCreatedAt($value)
 * @method static Builder<static>|ContactSubmission whereEmail($value)
 * @method static Builder<static>|ContactSubmission whereId($value)
 * @method static Builder<static>|ContactSubmission whereMessage($value)
 * @method static Builder<static>|ContactSubmission whereName($value)
 * @method static Builder<static>|ContactSubmission whereRepliedAt($value)
 * @method static Builder<static>|ContactSubmission whereStatus($value)
 * @method static Builder<static>|ContactSubmission whereSubject($value)
 * @method static Builder<static>|ContactSubmission whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
