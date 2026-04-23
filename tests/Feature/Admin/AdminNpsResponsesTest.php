<?php

use App\Enums\AuditEvent;
use App\Models\NpsResponse;
use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

// ── Auth ──────────────────────────────────────────────────────────────────────

it('requires admin to view NPS responses index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin/nps-responses')
        ->assertForbidden();
});

it('admin can view NPS responses index', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/nps-responses')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('App/Admin/NpsResponses/Index'));
});

// ── Summary Stats ──────────────────────────────────────────────────────────────

it('index returns correct category counts in summary', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    NpsResponse::factory()->count(3)->promoter()->create(['user_id' => $user->id]);
    NpsResponse::factory()->count(2)->passive()->create(['user_id' => $user->id]);
    NpsResponse::factory()->count(1)->detractor()->create(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->get('/admin/nps-responses')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('App/Admin/NpsResponses/Index')
            ->where('summary.promoters', 3)
            ->where('summary.passives', 2)
            ->where('summary.detractors', 1)
            ->where('summary.total', 6)
        );
});

it('index computes NPS score correctly', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // 3 promoters, 1 detractor, 1 passive → NPS = (3-1)/5 * 100 = 40
    NpsResponse::factory()->count(3)->promoter()->create(['user_id' => $user->id]);
    NpsResponse::factory()->count(1)->passive()->create(['user_id' => $user->id]);
    NpsResponse::factory()->count(1)->detractor()->create(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->get('/admin/nps-responses')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('summary.nps_score', 40)
        );
});

it('NPS score is null when no responses exist', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/nps-responses')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('summary.nps_score', null)
            ->where('summary.total', 0)
        );
});

// ── Filters ────────────────────────────────────────────────────────────────────

it('admin can filter NPS responses by category promoter', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/nps-responses?category=promoter')
        ->assertOk();
});

it('admin can filter NPS responses by category passive', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/nps-responses?category=passive')
        ->assertOk();
});

it('admin can filter NPS responses by category detractor', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/nps-responses?category=detractor')
        ->assertOk();
});

it('index validates invalid category enum', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/nps-responses?category=invalid')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['category']);
});

it('admin can filter by survey trigger', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/nps-responses?survey_trigger=quarterly')
        ->assertOk();
});

it('admin can search NPS responses', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/nps-responses?search=great')
        ->assertOk();
});

it('category filter returns only matching responses', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    NpsResponse::factory()->promoter()->create(['user_id' => $user->id]);
    NpsResponse::factory()->detractor()->create(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->get('/admin/nps-responses?category=promoter')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('responses.total', 1)
        );
});

// ── Query Count ───────────────────────────────────────────────────────────────

it('index page respects query count budget', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    NpsResponse::factory()->count(5)->create(['user_id' => $user->id]);

    DB::enableQueryLog();
    $this->actingAs($admin)
        ->get('/admin/nps-responses')
        ->assertOk();
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // paginate (2) + stats aggregation (1) + survey triggers (1) = 4 controller queries
    // auth/middleware queries happen before the controller, so filter to table queries
    $controllerQueries = array_filter($queries, fn ($q) => str_contains($q['query'], 'nps_responses'));
    expect(count($controllerQueries))->toBeLessThanOrEqual(5);
});

// ── Export ─────────────────────────────────────────────────────────────────────

it('requires admin to export NPS responses', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin/nps-responses/export')
        ->assertForbidden();
});

it('admin can export NPS responses as CSV', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    NpsResponse::factory()->promoter()->create([
        'user_id' => $user->id,
        'comment' => 'Great product!',
        'survey_trigger' => 'quarterly',
    ]);

    $response = $this->actingAs($admin)
        ->get('/admin/nps-responses/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('export logs an audit event', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/nps-responses/export');

    $this->assertDatabaseHas('audit_logs', [
        'event' => AuditEvent::ADMIN_NPS_EXPORTED->value,
        'user_id' => $admin->id,
    ]);
});

it('export validates invalid category', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/nps-responses/export?category=invalid')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['category']);
});

it('escapes LIKE wildcards in NPS response search', function () {
    $admin = User::factory()->admin()->create();

    NpsResponse::factory()->create(['score' => 9, 'comment' => 'test%feedback here']);
    NpsResponse::factory()->create(['score' => 5, 'comment' => 'testGeneral feedback']);

    // Literal % in search should match only the response whose comment contains "test%feedback"
    $response = $this->actingAs($admin)->get('/admin/nps-responses?search=test%25feedback');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('responses.data', 1)
            ->where('responses.data.0.comment', 'test%feedback here')
        );
});
