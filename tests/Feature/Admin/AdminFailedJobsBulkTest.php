<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    registerAdminRoutes();
});

function seedBulkFailedJob(string $queue = 'default'): string
{
    $uuid = (string) Str::uuid();
    DB::table('failed_jobs')->insert([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => $queue,
        'payload' => json_encode([
            'displayName' => 'App\\Jobs\\BulkTestJob',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
        ]),
        'exception' => 'RuntimeException: Bulk test',
        'failed_at' => now(),
    ]);

    return $uuid;
}

it('bulk retries selected failed jobs', function () {
    $admin = User::factory()->admin()->create();
    $uuid1 = seedBulkFailedJob();
    $uuid2 = seedBulkFailedJob();
    $uuid3 = seedBulkFailedJob();

    Artisan::shouldReceive('call')
        ->times(3)
        ->with('queue:retry', Mockery::on(fn ($args) => isset($args['id'])))
        ->andReturn(0);

    $response = $this->actingAs($admin)
        ->post('/admin/failed-jobs/bulk-retry', ['ids' => [$uuid1, $uuid2, $uuid3]]);

    $response->assertRedirect('/admin/failed-jobs');
    $response->assertSessionHas('success');
});

it('bulk deletes selected failed jobs', function () {
    $admin = User::factory()->admin()->create();
    $uuid1 = seedBulkFailedJob();
    $uuid2 = seedBulkFailedJob();
    $uuid3 = seedBulkFailedJob();

    $response = $this->actingAs($admin)
        ->delete('/admin/failed-jobs/bulk', ['ids' => [$uuid1, $uuid2, $uuid3]]);

    $response->assertRedirect('/admin/failed-jobs');
    $response->assertSessionHas('success');
    expect(DB::table('failed_jobs')->whereIn('uuid', [$uuid1, $uuid2, $uuid3])->count())->toBe(0);
});

it('bulk delete does not remove unselected jobs', function () {
    $admin = User::factory()->admin()->create();
    $uuid1 = seedBulkFailedJob();
    $uuid2 = seedBulkFailedJob();
    $unselected = seedBulkFailedJob();

    $this->actingAs($admin)
        ->delete('/admin/failed-jobs/bulk', ['ids' => [$uuid1, $uuid2]]);

    expect(DB::table('failed_jobs')->where('uuid', $unselected)->exists())->toBeTrue();
});

it('validates max 100 ids for bulk retry', function () {
    $admin = User::factory()->admin()->create();
    $ids = array_fill(0, 101, (string) Str::uuid());

    // Use postJson to get JSON validation response (422) rather than web redirect
    $response = $this->actingAs($admin)
        ->postJson('/admin/failed-jobs/bulk-retry', ['ids' => $ids]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['ids']);
});

it('validates max 100 ids for bulk delete', function () {
    $admin = User::factory()->admin()->create();
    $ids = array_fill(0, 101, (string) Str::uuid());

    // Use deleteJson to get JSON validation response (422) rather than web redirect
    $response = $this->actingAs($admin)
        ->deleteJson('/admin/failed-jobs/bulk', ['ids' => $ids]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['ids']);
});

it('bulk retry requires ids to be present', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->postJson('/admin/failed-jobs/bulk-retry', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['ids']);
});

it('bulk delete requires ids to be present', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->deleteJson('/admin/failed-jobs/bulk', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['ids']);
});

it('redirects non-admin from bulk retry', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/failed-jobs/bulk-retry', ['ids' => [(string) Str::uuid()]])
        ->assertStatus(403);
});

it('redirects non-admin from bulk delete', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/admin/failed-jobs/bulk', ['ids' => [(string) Str::uuid()]])
        ->assertStatus(403);
});
