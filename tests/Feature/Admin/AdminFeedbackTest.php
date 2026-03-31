<?php

use App\Enums\AnalyticsEvent;
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

    expect(AuditLog::where('event', AnalyticsEvent::ADMIN_FEEDBACK_UPDATED->value)
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

    expect(AuditLog::where('event', AnalyticsEvent::ADMIN_FEEDBACK_DELETED->value)
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
