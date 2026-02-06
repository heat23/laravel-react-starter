<?php

use App\Models\AuditLog;

test('prune deletes old records', function () {
    AuditLog::factory()->create(['created_at' => now()->subDays(100)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(50)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(10)]);

    $this->artisan('audit:prune', ['--days' => 90])
        ->expectsOutputToContain('Pruned 1 audit log records')
        ->assertExitCode(0);

    $this->assertCount(2, AuditLog::all());
});

test('prune preserves recent records', function () {
    AuditLog::factory()->create(['created_at' => now()->subDays(10)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(5)]);

    $this->artisan('audit:prune', ['--days' => 90])
        ->expectsOutputToContain('Pruned 0 audit log records')
        ->assertExitCode(0);

    $this->assertCount(2, AuditLog::all());
});

test('prune respects custom days', function () {
    AuditLog::factory()->create(['created_at' => now()->subDays(5)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(2)]);

    $this->artisan('audit:prune', ['--days' => 3])
        ->expectsOutputToContain('Pruned 1 audit log records')
        ->assertExitCode(0);

    $this->assertCount(1, AuditLog::all());
});
