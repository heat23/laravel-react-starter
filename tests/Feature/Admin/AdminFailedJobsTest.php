<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    registerAdminRoutes();
});

function seedFailedJob(array $overrides = []): int
{
    return DB::table('failed_jobs')->insertGetId(array_merge([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode([
            'displayName' => 'App\\Jobs\\TestJob',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => ['commandName' => 'App\\Jobs\\TestJob'],
        ]),
        'exception' => "RuntimeException: Something went wrong in /app/Jobs/TestJob.php:42\nStack trace:\n#0 ...",
        'failed_at' => now(),
    ], $overrides));
}

it('redirects guests to login', function () {
    $this->get('/admin/failed-jobs')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/failed-jobs')->assertStatus(403);
});

it('shows failed jobs list for admin', function () {
    $admin = User::factory()->admin()->create();
    seedFailedJob();

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/FailedJobs/Index')
        ->has('jobs.data', 1)
        ->where('jobs.data.0.queue', 'default')
        ->where('jobs.data.0.payload_summary', 'TestJob')
    );
});

it('shows empty list when no failed jobs', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $response->assertInertia(fn ($page) => $page
        ->has('jobs.data', 0)
    );
});

it('shows failed job detail', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    $response = $this->actingAs($admin)->get("/admin/failed-jobs/{$id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/FailedJobs/Show')
        ->where('job.id', $id)
        ->where('job.queue', 'default')
        ->has('job.payload')
        ->has('job.exception')
    );
});

it('returns 404 for non-existent failed job', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/failed-jobs/99999')->assertStatus(404);
});

it('retries a failed job', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    Artisan::shouldReceive('call')
        ->once()
        ->with('queue:retry', \Mockery::on(fn ($args) => isset($args['id'])));

    $response = $this->actingAs($admin)->post("/admin/failed-jobs/{$id}/retry");

    $response->assertRedirect('/admin/failed-jobs');
    $response->assertSessionHas('success');
});

it('deletes a failed job', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    $response = $this->actingAs($admin)->delete("/admin/failed-jobs/{$id}");

    $response->assertRedirect('/admin/failed-jobs');
    expect(DB::table('failed_jobs')->where('id', $id)->exists())->toBeFalse();
});

it('returns 404 when retrying non-existent job', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/failed-jobs/99999/retry')->assertStatus(404);
});

it('returns 404 when deleting non-existent job', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete('/admin/failed-jobs/99999')->assertStatus(404);
});

it('filters by queue', function () {
    $admin = User::factory()->admin()->create();
    seedFailedJob(['queue' => 'default']);
    seedFailedJob(['queue' => 'emails']);

    $response = $this->actingAs($admin)->get('/admin/failed-jobs?queue=emails');

    $response->assertInertia(fn ($page) => $page
        ->has('jobs.data', 1)
        ->where('jobs.data.0.queue', 'emails')
    );
});

it('creates audit log on retry', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    Artisan::shouldReceive('call')->once();

    $this->actingAs($admin)->post("/admin/failed-jobs/{$id}/retry");

    expect(AuditLog::where('event', 'admin.failed_job.retry')->exists())->toBeTrue();
});

it('creates audit log on delete', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    $this->actingAs($admin)->delete("/admin/failed-jobs/{$id}");

    expect(AuditLog::where('event', 'admin.failed_job.delete')->exists())->toBeTrue();
});

it('paginates failed jobs', function () {
    $admin = User::factory()->admin()->create();
    for ($i = 0; $i < 30; $i++) {
        seedFailedJob();
    }

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $response->assertInertia(fn ($page) => $page
        ->has('jobs.data', 25)
        ->where('jobs.total', 30)
        ->where('jobs.last_page', 2)
    );
});
