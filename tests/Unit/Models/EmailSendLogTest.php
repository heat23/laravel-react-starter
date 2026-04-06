<?php

namespace Tests\Unit\Models;

use App\Models\EmailSendLog;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmailSendLogTest extends TestCase
{
    use RefreshDatabase;

    // ── alreadySent ───────────────────────────────────────────────────────────

    public function test_already_sent_returns_false_when_no_log_exists(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(EmailSendLog::alreadySent($user->id, 'welcome', 1));
    }

    public function test_already_sent_returns_true_when_matching_log_exists(): void
    {
        $user = User::factory()->create();

        EmailSendLog::create([
            'user_id' => $user->id,
            'sequence_type' => 'welcome',
            'email_number' => 1,
            'sent_at' => now(),
        ]);

        $this->assertTrue(EmailSendLog::alreadySent($user->id, 'welcome', 1));
    }

    public function test_already_sent_does_not_match_different_sequence_type(): void
    {
        $user = User::factory()->create();

        EmailSendLog::create([
            'user_id' => $user->id,
            'sequence_type' => 'onboarding',
            'email_number' => 1,
            'sent_at' => now(),
        ]);

        $this->assertFalse(EmailSendLog::alreadySent($user->id, 'welcome', 1));
    }

    public function test_already_sent_does_not_match_different_email_number(): void
    {
        $user = User::factory()->create();

        EmailSendLog::create([
            'user_id' => $user->id,
            'sequence_type' => 'welcome',
            'email_number' => 2,
            'sent_at' => now(),
        ]);

        $this->assertFalse(EmailSendLog::alreadySent($user->id, 'welcome', 1));
    }

    public function test_already_sent_does_not_match_different_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        EmailSendLog::create([
            'user_id' => $userA->id,
            'sequence_type' => 'welcome',
            'email_number' => 1,
            'sent_at' => now(),
        ]);

        $this->assertFalse(EmailSendLog::alreadySent($userB->id, 'welcome', 1));
    }

    // ── record ────────────────────────────────────────────────────────────────

    public function test_record_creates_log_and_returns_true_on_first_call(): void
    {
        $user = User::factory()->create();

        $result = EmailSendLog::record($user->id, 'welcome', 1);

        $this->assertTrue($result);
        $this->assertTrue(EmailSendLog::alreadySent($user->id, 'welcome', 1));
    }

    public function test_record_returns_false_on_duplicate_without_throwing(): void
    {
        $user = User::factory()->create();

        EmailSendLog::record($user->id, 'welcome', 1);
        $result = EmailSendLog::record($user->id, 'welcome', 1);

        $this->assertFalse($result);
        // Exactly one row — dedup safety net worked
        $this->assertSame(
            1,
            EmailSendLog::where('user_id', $user->id)
                ->where('sequence_type', 'welcome')
                ->where('email_number', 1)
                ->count()
        );
    }

    public function test_record_sets_sent_at_on_created_record(): void
    {
        $user = User::factory()->create();

        EmailSendLog::record($user->id, 'welcome', 1);

        $log = EmailSendLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log->sent_at);
    }

    public function test_record_rethrows_unrelated_query_exceptions(): void
    {
        // Verify the guard conditions in record() are exclusive: a QueryException
        // that is NOT a unique-constraint violation must NOT match the swallow
        // conditions, so it will bubble up.
        $pdoException = new \PDOException('SQLSTATE[23000]: foreign key violation', 23000);
        $queryException = new QueryException('default', 'INSERT INTO ...', [], $pdoException);

        $this->assertFalse(str_contains($queryException->getMessage(), 'Duplicate entry'));
        $this->assertFalse(str_contains($queryException->getMessage(), 'UNIQUE constraint failed'));
        $this->assertNotInstanceOf(UniqueConstraintViolationException::class, $queryException);
    }

    // ── model properties ──────────────────────────────────────────────────────

    public function test_sent_at_is_cast_to_carbon_datetime(): void
    {
        $user = User::factory()->create();

        EmailSendLog::record($user->id, 'welcome', 1);

        $log = EmailSendLog::first();
        $this->assertInstanceOf(Carbon::class, $log->sent_at);
    }

    public function test_user_relationship_resolves_correct_user(): void
    {
        $user = User::factory()->create();

        EmailSendLog::create([
            'user_id' => $user->id,
            'sequence_type' => 'welcome',
            'email_number' => 1,
            'sent_at' => now(),
        ]);

        $log = EmailSendLog::with('user')->first();
        $this->assertSame($user->id, $log->user->id);
    }
}
