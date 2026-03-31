<?php

use App\Models\ContactSubmission;
use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

// --- Access control ---

it('redirects guests to login', function () {
    $this->get('/admin/contact-submissions')->assertRedirect('/login');
});

it('returns 403 for non-admin users on index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/contact-submissions')->assertStatus(403);
});

it('returns 403 for non-admin users on show', function () {
    $user = User::factory()->create();
    $submission = ContactSubmission::factory()->create();

    $this->actingAs($user)->get("/admin/contact-submissions/{$submission->id}")->assertStatus(403);
});

it('returns 403 for non-admin users on update', function () {
    $user = User::factory()->create();
    $submission = ContactSubmission::factory()->create();

    $this->actingAs($user)->patch("/admin/contact-submissions/{$submission->id}", ['status' => 'replied'])->assertStatus(403);
});

it('returns 403 for non-admin users on bulk update', function () {
    $user = User::factory()->create();
    $s = ContactSubmission::factory()->create();

    $this->actingAs($user)
        ->post('/admin/contact-submissions/bulk-update', ['ids' => [$s->id], 'action' => 'spam'])
        ->assertStatus(403);
});

it('returns 403 for non-super-admin on destroy', function () {
    $admin = User::factory()->admin()->create();
    $submission = ContactSubmission::factory()->create();

    $this->actingAs($admin)->delete("/admin/contact-submissions/{$submission->id}")->assertStatus(403);
});

it('returns 403 for non-admin on export', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/contact-submissions/export')->assertStatus(403);
});

// --- Index ---

it('renders index page for admin', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->count(3)->create(['status' => 'new']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ContactSubmissions/Index')
        ->has('submissions.data', 3)
        ->has('filters')
        ->has('counts')
    );
});

it('filters submissions by status', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->create(['status' => 'new']);
    ContactSubmission::factory()->create(['status' => 'spam']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions?status=spam');

    $response->assertInertia(fn ($page) => $page
        ->has('submissions.data', 1)
        ->where('submissions.data.0.status', 'spam')
    );
});

it('searches submissions by name', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->create(['name' => 'Alice Wonderland', 'status' => 'new']);
    ContactSubmission::factory()->create(['name' => 'Bob Builder', 'status' => 'new']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions?search=Alice');

    $response->assertInertia(fn ($page) => $page
        ->has('submissions.data', 1)
        ->where('submissions.data.0.name', 'Alice Wonderland')
    );
});

it('searches submissions by email', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->create(['email' => 'alice@example.com']);
    ContactSubmission::factory()->create(['email' => 'bob@example.com']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions?search=alice@example');

    $response->assertInertia(fn ($page) => $page
        ->has('submissions.data', 1)
        ->where('submissions.data.0.email', 'alice@example.com')
    );
});

it('searches submissions by subject', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->create(['subject' => 'Unique billing question']);
    ContactSubmission::factory()->create(['subject' => 'General inquiry']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions?search=billing');

    $response->assertInertia(fn ($page) => $page
        ->has('submissions.data', 1)
    );
});

it('sorts submissions by sort and dir params', function () {
    $admin = User::factory()->admin()->create();
    $older = ContactSubmission::factory()->create(['status' => 'new', 'created_at' => now()->subDays(2)]);
    $newer = ContactSubmission::factory()->create(['status' => 'new', 'created_at' => now()->subDay()]);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions?sort=created_at&dir=asc');

    $response->assertInertia(fn ($page) => $page
        ->where('submissions.data.0.id', $older->id)
        ->where('submissions.data.1.id', $newer->id)
    );
});

it('rejects invalid sort column with a validation error', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->count(2)->create();

    $response = $this->actingAs($admin)->get('/admin/contact-submissions?sort=injected_column');

    $response->assertSessionHasErrors('sort');
});

// --- Show ---

it('renders show page for admin', function () {
    $admin = User::factory()->admin()->create();
    $submission = ContactSubmission::factory()->create(['subject' => 'Test subject']);

    $response = $this->actingAs($admin)->get("/admin/contact-submissions/{$submission->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ContactSubmissions/Show')
        ->where('submission.id', $submission->id)
        ->where('submission.subject', 'Test subject')
    );
});

// --- Update ---

it('updates submission status to replied and sets replied_at', function () {
    $admin = User::factory()->admin()->create();
    $submission = ContactSubmission::factory()->create(['status' => 'new', 'replied_at' => null]);

    $this->actingAs($admin)
        ->patch("/admin/contact-submissions/{$submission->id}", ['status' => 'replied'])
        ->assertRedirect();

    $submission->refresh();
    expect($submission->status)->toBe('replied')
        ->and($submission->replied_at)->not->toBeNull();
});

it('clears replied_at when status changes away from replied', function () {
    $admin = User::factory()->admin()->create();
    $submission = ContactSubmission::factory()->create([
        'status' => 'replied',
        'replied_at' => now(),
    ]);

    $this->actingAs($admin)
        ->patch("/admin/contact-submissions/{$submission->id}", ['status' => 'spam'])
        ->assertRedirect();

    $submission->refresh();
    expect($submission->status)->toBe('spam')
        ->and($submission->replied_at)->toBeNull();
});

it('does not overwrite replied_at when status remains replied', function () {
    $admin = User::factory()->admin()->create();
    $originalRepliedAt = now()->subHours(2);
    $submission = ContactSubmission::factory()->create([
        'status' => 'replied',
        'replied_at' => $originalRepliedAt,
    ]);

    $this->actingAs($admin)
        ->patch("/admin/contact-submissions/{$submission->id}", ['status' => 'replied'])
        ->assertRedirect();

    $submission->refresh();
    expect($submission->replied_at->toDateTimeString())->toBe($originalRepliedAt->toDateTimeString());
});

it('update rejects invalid status', function () {
    $admin = User::factory()->admin()->create();
    $submission = ContactSubmission::factory()->create(['status' => 'new']);

    $this->actingAs($admin)
        ->patch("/admin/contact-submissions/{$submission->id}", ['status' => 'invalid_status'])
        ->assertSessionHasErrors('status');
});

// --- Bulk Update ---

it('bulk marks submissions as spam', function () {
    $admin = User::factory()->admin()->create();
    $s1 = ContactSubmission::factory()->create(['status' => 'new']);
    $s2 = ContactSubmission::factory()->create(['status' => 'new']);

    $this->actingAs($admin)
        ->post('/admin/contact-submissions/bulk-update', [
            'ids' => [$s1->id, $s2->id],
            'action' => 'spam',
        ])
        ->assertRedirect();

    expect($s1->fresh()->status)->toBe('spam')
        ->and($s2->fresh()->status)->toBe('spam');
});

it('bulk marks submissions as replied and sets replied_at', function () {
    $admin = User::factory()->admin()->create();
    $s1 = ContactSubmission::factory()->create(['status' => 'new', 'replied_at' => null]);
    $s2 = ContactSubmission::factory()->create(['status' => 'new', 'replied_at' => null]);

    $this->actingAs($admin)
        ->post('/admin/contact-submissions/bulk-update', [
            'ids' => [$s1->id, $s2->id],
            'action' => 'replied',
        ])
        ->assertRedirect();

    expect($s1->fresh()->status)->toBe('replied')
        ->and($s1->fresh()->replied_at)->not->toBeNull()
        ->and($s2->fresh()->replied_at)->not->toBeNull();
});

it('bulk delete removes submissions (super_admin)', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $s1 = ContactSubmission::factory()->create();
    $s2 = ContactSubmission::factory()->create();

    $this->actingAs($superAdmin)
        ->post('/admin/contact-submissions/bulk-update', [
            'ids' => [$s1->id, $s2->id],
            'action' => 'delete',
        ])
        ->assertRedirect();

    expect(ContactSubmission::find($s1->id))->toBeNull()
        ->and(ContactSubmission::find($s2->id))->toBeNull();
});

it('bulk update rejects invalid action', function () {
    $admin = User::factory()->admin()->create();
    $s = ContactSubmission::factory()->create();

    $this->actingAs($admin)
        ->post('/admin/contact-submissions/bulk-update', [
            'ids' => [$s->id],
            'action' => 'invalid_action',
        ])
        ->assertSessionHasErrors('action');
});

it('bulk update rejects empty ids', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/contact-submissions/bulk-update', [
            'ids' => [],
            'action' => 'spam',
        ])
        ->assertSessionHasErrors('ids');
});

// --- Destroy ---

it('super admin can delete a submission', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $submission = ContactSubmission::factory()->create();

    $this->actingAs($superAdmin)
        ->delete("/admin/contact-submissions/{$submission->id}")
        ->assertRedirect(route('admin.contact-submissions.index'));

    expect(ContactSubmission::find($submission->id))->toBeNull();
});

// --- Export ---

it('admin can export submissions as CSV', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->count(3)->create(['status' => 'new']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions/export');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('export filters by status', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->count(2)->create(['status' => 'spam']);
    ContactSubmission::factory()->count(3)->create(['status' => 'new']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions/export?status=spam');

    $response->assertStatus(200);
    $content = $response->streamedContent();
    $lines = array_filter(explode("\n", trim($content)));
    // 1 header + 2 spam rows = 3 lines
    expect(count($lines))->toBe(3);
});

it('export filters by search term', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->create(['name' => 'Alice Smith']);
    ContactSubmission::factory()->create(['name' => 'Bob Jones']);

    $response = $this->actingAs($admin)->get('/admin/contact-submissions/export?search=Alice');

    $response->assertStatus(200);
    $content = $response->streamedContent();
    $lines = array_filter(explode("\n", trim($content)));
    // 1 header + 1 matching row
    expect(count($lines))->toBe(2);
});
