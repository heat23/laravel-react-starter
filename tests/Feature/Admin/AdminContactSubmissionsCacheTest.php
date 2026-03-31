<?php

use App\Enums\AdminCacheKey;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    registerAdminRoutes();
    Cache::forget(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value);
});

it('index caches contact submission counts', function () {
    $admin = User::factory()->admin()->create();
    ContactSubmission::factory()->create(['status' => 'new']);
    ContactSubmission::factory()->create(['status' => 'replied', 'replied_at' => now()]);

    expect(Cache::has(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value))->toBeFalse();

    $this->actingAs($admin)->get('/admin/contact-submissions');

    expect(Cache::has(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value))->toBeTrue();

    $counts = Cache::get(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value);
    expect($counts['new'])->toBe(1)
        ->and($counts['replied'])->toBe(1)
        ->and($counts['spam'])->toBe(0);
});

it('update invalidates contact submissions stats cache', function () {
    $admin = User::factory()->admin()->create();
    $submission = ContactSubmission::factory()->create(['status' => 'new']);

    Cache::put(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value, ['new' => 1, 'replied' => 0, 'spam' => 0], 300);

    $this->actingAs($admin)
        ->patch("/admin/contact-submissions/{$submission->id}", ['status' => 'replied'])
        ->assertRedirect();

    expect(Cache::has(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value))->toBeFalse();
});

it('bulkUpdate invalidates contact submissions stats cache', function () {
    $admin = User::factory()->admin()->create();
    $s1 = ContactSubmission::factory()->create(['status' => 'new']);
    $s2 = ContactSubmission::factory()->create(['status' => 'new']);

    Cache::put(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value, ['new' => 2, 'replied' => 0, 'spam' => 0], 300);

    $this->actingAs($admin)
        ->post('/admin/contact-submissions/bulk-update', [
            'ids' => [$s1->id, $s2->id],
            'action' => 'spam',
        ])
        ->assertRedirect();

    expect(Cache::has(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value))->toBeFalse();
});

it('destroy invalidates contact submissions stats cache', function () {
    $admin = User::factory()->superAdmin()->create();
    $submission = ContactSubmission::factory()->create(['status' => 'new']);

    Cache::put(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value, ['new' => 1, 'replied' => 0, 'spam' => 0], 300);

    $this->actingAs($admin)
        ->delete("/admin/contact-submissions/{$submission->id}")
        ->assertRedirect();

    expect(Cache::has(AdminCacheKey::CONTACT_SUBMISSIONS_STATS->value))->toBeFalse();
});
