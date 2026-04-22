<?php

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    registerAdminRoutes();
});

function makeFeedback(array $overrides = []): Feedback
{
    return Feedback::create(array_merge([
        'type' => 'bug',
        'message' => 'Something broke.',
        'status' => 'open',
    ], $overrides));
}

// ── Auth ──────────────────────────────────────────────────────────────────────

it('requires admin to view feedback index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin/feedback')
        ->assertForbidden();
});

it('admin can view feedback index', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/feedback')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Feedback/Index'));
});

it('admin can filter feedback by type', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/feedback?type=bug')
        ->assertOk();
});

it('admin can filter feedback by status', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/feedback?status=open')
        ->assertOk();
});

it('index validates type enum', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/feedback?type=invalid')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

it('index validates status enum', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/feedback?status=invalid')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('rejects invalid per_page value for feedback index', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/feedback?per_page=9999')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});

it('accepts valid per_page values for feedback index', function () {
    $admin = User::factory()->admin()->create();
    foreach ([10, 25, 50, 100] as $value) {
        $this->actingAs($admin)
            ->getJson("/admin/feedback?per_page={$value}")
            ->assertOk();
    }
});

it('feedback index passes per_page filter back to frontend', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/feedback?per_page=10')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Feedback/Index')
            ->where('filters.per_page', '10')
        );
});

it('feedback index always includes resolved per_page in filters even without query param', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/feedback')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Feedback/Index')
            ->where('filters.per_page', (string) config('pagination.admin.feedback', 50))
        );
});

it('admin can view a feedback item', function () {
    $admin = User::factory()->admin()->create();
    $feedback = makeFeedback();

    $this->actingAs($admin)
        ->get("/admin/feedback/{$feedback->id}")
        ->assertOk();
});

// ── Update ────────────────────────────────────────────────────────────────────

it('admin can update feedback status', function () {
    $admin = User::factory()->admin()->create();
    $feedback = makeFeedback(['status' => 'open']);

    $this->actingAs($admin)
        ->patch("/admin/feedback/{$feedback->id}", ['status' => 'resolved'])
        ->assertRedirect();

    expect($feedback->fresh()->status)->toBe('resolved');
    expect($feedback->fresh()->resolved_at)->not->toBeNull();
});

it('reopening resolved feedback clears resolved_at', function () {
    $admin = User::factory()->admin()->create();
    $feedback = makeFeedback(['status' => 'resolved', 'resolved_at' => now()->subHour()]);

    $this->actingAs($admin)
        ->patch("/admin/feedback/{$feedback->id}", ['status' => 'open']);

    expect($feedback->fresh()->resolved_at)->toBeNull();
});

it('update logs an audit event', function () {
    $admin = User::factory()->admin()->create();
    $feedback = makeFeedback();

    $this->actingAs($admin)
        ->patch("/admin/feedback/{$feedback->id}", ['status' => 'in_review']);

    expect(AuditLog::where('event', AuditEvent::ADMIN_FEEDBACK_UPDATED->value)
        ->whereJsonContains('metadata->feedback_id', $feedback->id)
        ->exists()
    )->toBeTrue();
});

it('update validates status enum', function () {
    $admin = User::factory()->admin()->create();
    $feedback = makeFeedback();

    $this->actingAs($admin)
        ->patchJson("/admin/feedback/{$feedback->id}", ['status' => 'invalid'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

// ── Destroy ───────────────────────────────────────────────────────────────────

it('super admin can delete feedback', function () {
    $admin = User::factory()->superAdmin()->create();
    $feedback = makeFeedback();

    $this->actingAs($admin)
        ->delete("/admin/feedback/{$feedback->id}")
        ->assertRedirect('/admin/feedback');

    expect(Feedback::find($feedback->id))->toBeNull();
});

it('regular admin cannot delete feedback', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $feedback = makeFeedback();

    $this->actingAs($admin)
        ->delete("/admin/feedback/{$feedback->id}")
        ->assertForbidden();
});

it('destroy logs an audit event', function () {
    $admin = User::factory()->superAdmin()->create();
    $feedback = makeFeedback();
    $feedbackId = $feedback->id;

    $this->actingAs($admin)
        ->delete("/admin/feedback/{$feedback->id}");

    expect(AuditLog::where('event', AuditEvent::ADMIN_FEEDBACK_DELETED->value)
        ->whereJsonContains('metadata->feedback_id', $feedbackId)
        ->exists()
    )->toBeTrue();
});

it('feedback from soft-deleted users loads correctly on index', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    makeFeedback(['user_id' => $user->id]);
    $user->delete(); // soft-delete

    $this->actingAs($admin)
        ->get('/admin/feedback')
        ->assertOk();
});

// ── Export ────────────────────────────────────────────────────────────────────

it('export requires admin', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin/feedback/export')
        ->assertForbidden();
});

it('export returns csv for admin', function () {
    $admin = User::factory()->admin()->create();
    makeFeedback(['type' => 'bug', 'status' => 'open', 'message' => 'a reproducible crash']);

    $response = $this->actingAs($admin)
        ->get('/admin/feedback/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename=feedback-'.now()->format('Y-m-d').'.csv');

    $body = $response->streamedContent();
    expect(strlen($body))->toBeGreaterThan(0);
    expect($body)->toContain('ID,Type,Status,Priority,Message')
        ->and($body)->toContain('bug')
        ->and($body)->toContain('a reproducible crash');
});

it('export returns only header row when no records match filters', function () {
    $admin = User::factory()->admin()->create();
    makeFeedback(['type' => 'bug', 'status' => 'open']);

    $response = $this->actingAs($admin)
        ->get('/admin/feedback/export?type=feature&status=resolved');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename=feedback-'.now()->format('Y-m-d').'.csv');

    $body = $response->streamedContent();
    expect(strlen($body))->toBeGreaterThan(0);
    $lines = array_filter(explode("\n", trim($body)));
    expect($body)->toContain('ID,Type,Status,Priority,Message')
        ->and(count($lines))->toBe(1);
});

it('export validates type enum', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/feedback/export?type=invalid')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

it('export validates status enum', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/feedback/export?status=invalid')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('export rejects search exceeding max length', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/feedback/export?search='.str_repeat('a', 101))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['search']);
});

it('export accepts valid type and status filters', function () {
    $admin = User::factory()->admin()->create();
    makeFeedback(['type' => 'feature', 'status' => 'in_review', 'message' => 'matching record']);
    makeFeedback(['type' => 'bug', 'status' => 'open', 'message' => 'excluded record']);

    $response = $this->actingAs($admin)
        ->get('/admin/feedback/export?type=feature&status=in_review');

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'attachment; filename=feedback-'.now()->format('Y-m-d').'.csv');

    $body = $response->streamedContent();
    expect(strlen($body))->toBeGreaterThan(0);
    expect($body)->toContain('matching record')
        ->and($body)->not->toContain('excluded record');
});

// ── Cache invalidation ────────────────────────────────────────────────────────

it('update invalidates the dashboard stats cache', function () {
    $admin = User::factory()->admin()->create();
    $feedback = makeFeedback();
    Cache::put('admin:dashboard:stats', ['stale' => true], 300);

    $this->actingAs($admin)
        ->patch("/admin/feedback/{$feedback->id}", ['status' => 'in_review']);

    expect(Cache::has('admin:dashboard:stats'))->toBeFalse();
});

it('bulkUpdate invalidates the dashboard stats cache', function () {
    $admin = User::factory()->superAdmin()->create();
    $f1 = makeFeedback();
    $f2 = makeFeedback();
    Cache::put('admin:dashboard:stats', ['stale' => true], 300);

    $this->actingAs($admin)
        ->post('/admin/feedback/bulk-update', ['ids' => [$f1->id, $f2->id], 'action' => 'resolve']);

    expect(Cache::has('admin:dashboard:stats'))->toBeFalse();
});

it('destroy invalidates the dashboard stats cache', function () {
    $admin = User::factory()->superAdmin()->create();
    $feedback = makeFeedback();
    Cache::put('admin:dashboard:stats', ['stale' => true], 300);

    $this->actingAs($admin)
        ->delete("/admin/feedback/{$feedback->id}");

    expect(Cache::has('admin:dashboard:stats'))->toBeFalse();
});

it('escapes LIKE wildcards in feedback search', function () {
    $admin = User::factory()->admin()->create();

    Feedback::create(['type' => 'bug', 'message' => 'test%issue with login', 'status' => 'open']);
    Feedback::create(['type' => 'bug', 'message' => 'testGeneral issue', 'status' => 'open']);

    // Literal % should match only the feedback whose message contains "test%issue"
    $response = $this->actingAs($admin)->get('/admin/feedback?search=test%25issue');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('feedback.data', 1)
            ->where('feedback.data.0.message', 'test%issue with login')
        );
});
