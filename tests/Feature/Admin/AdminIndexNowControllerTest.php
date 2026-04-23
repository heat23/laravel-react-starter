<?php

use App\Jobs\SubmitIndexNowUrlsJob;
use App\Models\IndexNowSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config([
        'features.indexnow.enabled' => true,
        'indexnow.key' => 'testkeytestkeytestkeytestkey1234',
        'indexnow.host' => 'example.test',
        'app.url' => 'https://example.test',
    ]);
});

it('redirects guests from the dashboard', function () {
    $response = $this->get('/admin/indexnow');
    $response->assertRedirect('/login');
});

it('rejects non-admin users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/admin/indexnow');

    $response->assertForbidden();
});

it('renders the dashboard for admins with stats + submissions', function () {
    IndexNowSubmission::factory()->success()->create();
    IndexNowSubmission::factory()->failed()->create();
    IndexNowSubmission::factory()->create(['status' => 'pending']);

    $response = adminGet('/admin/indexnow');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('App/Admin/IndexNow/Dashboard')
            ->has('stats')
            ->where('stats.total_submissions_30d', 3)
            ->where('stats.successful_submissions_30d', 1)
            ->where('stats.failed_submissions_30d', 1)
            ->where('stats.pending_submissions_30d', 1)
            ->where('configured', true)
            ->has('submissions.data', 3)
        );
});

it('filters submissions by status', function () {
    IndexNowSubmission::factory()->success()->count(2)->create();
    IndexNowSubmission::factory()->failed()->count(1)->create();

    $response = adminGet('/admin/indexnow', ['status' => 'failed']);

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('App/Admin/IndexNow/Dashboard')
            ->has('submissions.data', 1)
            ->where('filters.status', 'failed')
        );
});

it('rejects invalid filter values', function () {
    IndexNowSubmission::factory()->create(['status' => 'pending']);

    $response = adminGet('/admin/indexnow', ['status' => 'not-a-status']);

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters', [])
        );
});

it('shows a submission detail page', function () {
    $submission = IndexNowSubmission::factory()->create([
        'urls' => ['https://example.test/a', 'https://example.test/b'],
        'url_count' => 2,
    ]);

    $response = adminGet("/admin/indexnow/{$submission->id}");

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('App/Admin/IndexNow/SubmissionDetail')
            ->where('submission.id', $submission->id)
            ->where('submission.url_count', 2)
            ->has('submission.urls', 2)
        );
});

it('returns 404 for all routes when feature is disabled', function () {
    config(['features.indexnow.enabled' => false]);

    $response = adminGet('/admin/indexnow');
    $response->assertNotFound();
});

it('re-queues a failed submission via retry', function () {
    Queue::fake();

    $original = IndexNowSubmission::factory()->failed()->create([
        'urls' => ['https://example.test/a'],
        'url_count' => 1,
        'trigger' => 'sitemap',
    ]);

    $admin = User::factory()->admin()->superAdmin()->create();

    $response = adminPost("/admin/indexnow/{$original->id}/retry", [], $admin);

    $response->assertRedirect();
    expect(IndexNowSubmission::count())->toBe(2);

    $retry = IndexNowSubmission::where('id', '!=', $original->id)->first();
    expect($retry->status)->toBe('pending')
        ->and($retry->url_count)->toBe(1)
        ->and($retry->trigger)->toBe('sitemap:retry');

    Queue::assertPushed(SubmitIndexNowUrlsJob::class, 1);
});

it('refuses to retry non-failed submissions', function () {
    $submission = IndexNowSubmission::factory()->success()->create();
    $admin = User::factory()->admin()->superAdmin()->create();

    $response = adminPost("/admin/indexnow/{$submission->id}/retry", [], $admin);

    $response->assertStatus(422);
});

it('truncates long triggers when appending :retry suffix to stay under 50 chars', function () {
    // Adversarial finding: original trigger at 45+ chars + ':retry' overflows
    // VARCHAR(50) and MySQL silently truncates, corrupting the audit trail.
    Queue::fake();

    $longTrigger = str_repeat('a', 48); // 48 chars, valid per column
    $original = IndexNowSubmission::factory()->failed()->create([
        'trigger' => $longTrigger,
    ]);

    $admin = User::factory()->admin()->superAdmin()->create();

    adminPost("/admin/indexnow/{$original->id}/retry", [], $admin);

    $retry = IndexNowSubmission::where('id', '!=', $original->id)->first();
    expect(strlen($retry->trigger))->toBeLessThanOrEqual(50)
        ->and($retry->trigger)->toEndWith(':retry');
});
