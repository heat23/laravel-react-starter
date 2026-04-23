<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $sequence_type
 * @property int $email_number
 * @property Carbon $sent_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog whereEmailNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog whereSequenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailSendLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
class EmailSendLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'sequence_type',
        'email_number',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a sequence email was already sent to a user.
     */
    public static function alreadySent(int $userId, string $sequenceType, int $emailNumber): bool
    {
        return static::where('user_id', $userId)
            ->where('sequence_type', $sequenceType)
            ->where('email_number', $emailNumber)
            ->exists();
    }

    /**
     * Record a sent email. Returns false if already recorded (duplicate).
     */
    public static function record(int $userId, string $sequenceType, int $emailNumber): bool
    {
        try {
            static::create([
                'user_id' => $userId,
                'sequence_type' => $sequenceType,
                'email_number' => $emailNumber,
                'sent_at' => now(),
            ]);

            return true;
        } catch (UniqueConstraintViolationException) {
            return false;
        } catch (QueryException $e) {
            // Catch MySQL duplicate entry error (code 1062) as a safety net
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return false;
            }
            throw $e;
        }
    }
}
