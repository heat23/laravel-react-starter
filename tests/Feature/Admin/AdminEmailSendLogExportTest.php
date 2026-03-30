<?php

use App\Enums\AnalyticsEvent;
use App\Models\EmailSendLog;
use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('requires admin to export email send logs', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin/email-send-logs/export')
        ->assertForbidden();
});

it('admin can export email send logs as CSV', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    EmailSendLog::create([
        'user_id' => $user->id,
        'sequence_type' => 'welcome',
        'email_number' => 1,
        'sent_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get('/admin/email-send-logs/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('export logs an audit event', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/email-send-logs/export');

    $this->assertDatabaseHas('audit_logs', [
        'event' => AnalyticsEvent::ADMIN_EMAIL_SEND_LOGS_EXPORTED->value,
        'user_id' => $admin->id,
    ]);
});

it('export filters by sequence type', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    EmailSendLog::create(['user_id' => $user->id, 'sequence_type' => 'welcome', 'email_number' => 1, 'sent_at' => now()]);
    EmailSendLog::create(['user_id' => $user->id, 'sequence_type' => 'onboarding', 'email_number' => 1, 'sent_at' => now()]);

    $response = $this->actingAs($admin)
        ->get('/admin/email-send-logs/export?sequence_type=welcome');

    $response->assertOk();
    $content = $response->streamedContent();
    expect($content)->toContain('welcome')
        ->not->toContain('onboarding');
});
