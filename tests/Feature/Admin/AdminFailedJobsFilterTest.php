<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('filters failed jobs by queue', function () {
    $admin = User::factory()->admin()->create();
    DB::table('failed_jobs')->insert([
        ['uuid' => Str::uuid(), 'connection' => 'sync', 'queue' => 'default', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
        ['uuid' => Str::uuid(), 'connection' => 'sync', 'queue' => 'emails', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
    ]);

    $response = $this->actingAs($admin)
        ->get('/admin/failed-jobs?queue=emails');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('jobs.data', 1)
        ->where('jobs.data.0.queue', 'emails')
    );
});

it('passes distinct queue list to the view', function () {
    $admin = User::factory()->admin()->create();
    DB::table('failed_jobs')->insert([
        ['uuid' => Str::uuid(), 'connection' => 'sync', 'queue' => 'default', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
        ['uuid' => Str::uuid(), 'connection' => 'sync', 'queue' => 'emails', 'payload' => '{}', 'exception' => '', 'failed_at' => now()],
    ]);

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('queues', 2)
        ->where('queues.0', 'default')
        ->where('queues.1', 'emails')
    );
});
