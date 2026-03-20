<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('filters failed jobs by queue', function () {
    $admin = User::factory()->admin()->create();
    DB::table('failed_jobs')->insert([
        ['connection' => 'sync', 'queue' => 'default', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
        ['connection' => 'sync', 'queue' => 'emails', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
    ]);

    $response = $this->actingAs($admin)
        ->get('/admin/failed-jobs?queue=emails');

    $response->assertOk();
    $jobs = $response->inertia()->prop('jobs.data');
    expect(collect($jobs)->every(fn ($j) => $j['queue'] === 'emails'))->toBeTrue();
});

it('passes distinct queue list to the view', function () {
    $admin = User::factory()->admin()->create();
    DB::table('failed_jobs')->insert([
        ['connection' => 'sync', 'queue' => 'default', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
        ['connection' => 'sync', 'queue' => 'emails', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
    ]);

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $queues = $response->inertia()->prop('queues');
    expect($queues)->toContain('default')->toContain('emails');
});
