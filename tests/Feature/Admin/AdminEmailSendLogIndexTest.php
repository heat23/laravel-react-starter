<?php

use App\Models\EmailSendLog;
use App\Models\User;

it('requires authentication to view email send logs index', function () {
    $this->get('/admin/email-send-logs')
        ->assertRedirect('/login');
});

it('requires admin to view email send logs index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/email-send-logs')
        ->assertForbidden();
});

it('admin can view email send logs index', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    EmailSendLog::create([
        'user_id' => $user->id,
        'sequence_type' => 'welcome',
        'email_number' => 1,
        'sent_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin/email-send-logs')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/EmailSendLogs/Index')
            ->has('logs')
            ->has('sequenceTypes')
            ->has('filters')
        );
});

it('index filters by sequence type', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    EmailSendLog::create(['user_id' => $user->id, 'sequence_type' => 'welcome', 'email_number' => 1, 'sent_at' => now()]);
    EmailSendLog::create(['user_id' => $user->id, 'sequence_type' => 'onboarding', 'email_number' => 1, 'sent_at' => now()]);

    $this->actingAs($admin)
        ->get('/admin/email-send-logs?sequence_type=welcome')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('logs.data', 1)
            ->where('logs.data.0.sequence_type', 'welcome')
        );
});

it('index filters by search on user name', function () {
    $admin = User::factory()->admin()->create();
    $matchedUser = User::factory()->create(['name' => 'SearchableUser', 'email' => 'other@example.com']);
    $otherUser = User::factory()->create(['name' => 'OtherPerson', 'email' => 'nope@example.com']);

    EmailSendLog::create(['user_id' => $matchedUser->id, 'sequence_type' => 'welcome', 'email_number' => 1, 'sent_at' => now()]);
    EmailSendLog::create(['user_id' => $otherUser->id, 'sequence_type' => 'welcome', 'email_number' => 1, 'sent_at' => now()]);

    $this->actingAs($admin)
        ->get('/admin/email-send-logs?search=SearchableUser')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('logs.data', 1));
});

it('index filters by search on user email', function () {
    $admin = User::factory()->admin()->create();
    $matchedUser = User::factory()->create(['email' => 'findme@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);

    EmailSendLog::create(['user_id' => $matchedUser->id, 'sequence_type' => 'welcome', 'email_number' => 1, 'sent_at' => now()]);
    EmailSendLog::create(['user_id' => $otherUser->id, 'sequence_type' => 'welcome', 'email_number' => 1, 'sent_at' => now()]);

    $this->actingAs($admin)
        ->get('/admin/email-send-logs?search=findme')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('logs.data', 1));
});

it('index supports sorting by sent_at ascending', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $old = EmailSendLog::create(['user_id' => $user->id, 'sequence_type' => 'welcome', 'email_number' => 1, 'sent_at' => now()->subDays(2)]);
    $new = EmailSendLog::create(['user_id' => $user->id, 'sequence_type' => 'welcome', 'email_number' => 2, 'sent_at' => now()]);

    $this->actingAs($admin)
        ->get('/admin/email-send-logs?sort=sent_at&dir=asc')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('logs.data.0.id', $old->id)
        );
});

it('index paginates results', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Create more logs than a single page holds (default 50, use 3 per page for test)
    for ($i = 1; $i <= 3; $i++) {
        EmailSendLog::create([
            'user_id' => $user->id,
            'sequence_type' => 'welcome',
            'email_number' => $i,
            'sent_at' => now()->subMinutes($i),
        ]);
    }

    config(['pagination.admin.email_send_logs' => 2]);

    $this->actingAs($admin)
        ->get('/admin/email-send-logs?page=1')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('logs.data', 2)
            ->where('logs.total', 3)
            ->where('logs.last_page', 2)
        );
});

it('index returns empty results when no logs exist', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/email-send-logs')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('logs.data', 0));
});

it('index passes back active filters', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/email-send-logs?search=foo&sequence_type=welcome&sort=sent_at&dir=asc')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.search', 'foo')
            ->where('filters.sequence_type', 'welcome')
            ->where('filters.sort', 'sent_at')
            ->where('filters.dir', 'asc')
        );
});
