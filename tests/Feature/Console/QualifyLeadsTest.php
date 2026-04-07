<?php

use App\Events\LeadQualifiedEvent;
use App\Models\User;
use App\Services\LeadScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    // MQL = 60, SQL = 80 (constants from LeadScoringService)
});

it('dispatches MQL qualification event when a user crosses the MQL threshold', function () {
    Event::fake([LeadQualifiedEvent::class]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 50, // below MQL threshold of 60
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturn(65); // above MQL, below SQL
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    Event::assertDispatched(LeadQualifiedEvent::class, function ($event) use ($user) {
        return $event->user->id === $user->id
            && $event->stage === 'mql'
            && $event->score === 65;
    });
});

it('dispatches SQL qualification event when a user crosses the SQL threshold', function () {
    Event::fake([LeadQualifiedEvent::class]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 70, // below SQL threshold of 80
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturn(85); // above SQL
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    Event::assertDispatched(LeadQualifiedEvent::class, function ($event) use ($user) {
        return $event->user->id === $user->id && $event->stage === 'sql';
    });
});

it('does not dispatch an event when score stays below thresholds', function () {
    Event::fake([LeadQualifiedEvent::class]);

    User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 30,
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturn(40); // still below MQL
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    Event::assertNotDispatched(LeadQualifiedEvent::class);
});

it('does not dispatch an event when score was already above the threshold', function () {
    Event::fake([LeadQualifiedEvent::class]);

    User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 65, // already above MQL (60)
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturn(70); // still above MQL but no upward crossing
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    Event::assertNotDispatched(LeadQualifiedEvent::class);
});

it('persists the updated lead_score to the database', function () {
    Event::fake([LeadQualifiedEvent::class]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 20,
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturn(45);
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    expect(DB::table('users')->where('id', $user->id)->value('lead_score'))->toBe(45);
});

it('skips unverified users', function () {
    Event::fake([LeadQualifiedEvent::class]);

    User::factory()->unverified()->create(['lead_score' => 0]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->never();
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')
        ->expectsOutputToContain('Processed 0 leads')
        ->assertExitCode(0);
});

it('continues processing other users when one throws an exception', function () {
    Event::fake([LeadQualifiedEvent::class]);

    $user1 = User::factory()->create(['email_verified_at' => now(), 'lead_score' => 0]);
    $user2 = User::factory()->create(['email_verified_at' => now(), 'lead_score' => 0]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturnUsing(function (User $u) use ($user1) {
        if ($u->id === $user1->id) {
            throw new RuntimeException('scoring failure');
        }

        return 30;
    });
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')
        ->expectsOutputToContain('Processed 1 leads')
        ->assertExitCode(0);
});

it('sets lead_qualified_at on first MQL qualification', function () {
    Event::fake([LeadQualifiedEvent::class]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 50,
        'lead_qualified_at' => null,
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturn(65);
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    expect(DB::table('users')->where('id', $user->id)->value('lead_qualified_at'))->not->toBeNull();
});

it('sets lead_qualified_at when a user jumps directly from below-MQL to above-SQL without crossing MQL first', function () {
    Event::fake([LeadQualifiedEvent::class]);

    // previous=0 (below MQL=60), new score=85 (above SQL=80): SQL branch fires, MQL branch never runs
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 0,
        'lead_qualified_at' => null,
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    $scoreMock->shouldReceive('score')->andReturn(85);
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    // SQL event dispatched
    Event::assertDispatched(LeadQualifiedEvent::class, function ($event) use ($user) {
        return $event->user->id === $user->id && $event->stage === 'sql';
    });

    // lead_qualified_at must be stamped even though MQL branch was skipped
    expect(DB::table('users')->where('id', $user->id)->value('lead_qualified_at'))->not->toBeNull();
});

it('does not overwrite lead_qualified_at on subsequent qualification runs', function () {
    Event::fake([LeadQualifiedEvent::class]);

    // User was already MQL-qualified with a known timestamp
    $originalQualifiedAt = now()->subDays(10)->toDateTimeString();
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'lead_score' => 65, // already above MQL threshold of 60
        'lead_qualified_at' => $originalQualifiedAt,
    ]);

    $scoreMock = Mockery::mock(LeadScoringService::class);
    $scoreMock->shouldReceive('getMqlThreshold')->andReturn(60);
    $scoreMock->shouldReceive('getSqlThreshold')->andReturn(80);
    // Score dips and re-crosses MQL — previous score stored is 65 (above MQL), so no
    // upward crossing event fires. But even if score logic changed, the DB update uses
    // whereNull('lead_qualified_at'), so the original timestamp must be preserved.
    $scoreMock->shouldReceive('score')->andReturn(70); // still above MQL, no upward crossing
    $this->app->instance(LeadScoringService::class, $scoreMock);

    $this->artisan('emails:qualify-leads')->assertExitCode(0);

    // The original first-qualification timestamp must be unchanged
    expect(DB::table('users')->where('id', $user->id)->value('lead_qualified_at'))
        ->toBe($originalQualifiedAt);
});
