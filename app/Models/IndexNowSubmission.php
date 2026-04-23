<?php

namespace App\Models;

use Database\Factories\IndexNowSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property array<int, string>|null $urls
 * @property int $url_count
 * @property string $status
 * @property int|null $response_code
 * @property string|null $response_body
 * @property int $attempts
 * @property Carbon|null $submitted_at
 * @property string|null $trigger
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static \Database\Factories\IndexNowSubmissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IndexNowSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IndexNowSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IndexNowSubmission query()
 *
 * @mixin \Eloquent
 */
class IndexNowSubmission extends Model
{
    /** @use HasFactory<IndexNowSubmissionFactory> */
    use HasFactory;

    protected $table = 'indexnow_submissions';

    protected $fillable = [
        'uuid',
        'urls',
        'url_count',
        'status',
        'response_code',
        'response_body',
        'attempts',
        'submitted_at',
        'trigger',
    ];

    protected function casts(): array
    {
        return [
            'urls' => 'array',
            'submitted_at' => 'datetime',
        ];
    }
}
