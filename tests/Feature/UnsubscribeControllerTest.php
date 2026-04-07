<?php

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('renders the Unsubscribe page with the user email for a valid signed URL', function () {
    $user = User::factory()->create();
    $url = URL::signedRoute('unsubscribe', ['userId' => $user->id]);

    $this->get($url)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Unsubscribe')
            ->where('email', $user->email)
        );
});

it('sets marketing_emails to false for a valid signed URL', function () {
    $user = User::factory()->create();
    $url = URL::signedRoute('unsubscribe', ['userId' => $user->id]);

    $this->get($url)->assertOk();

    $value = UserSetting::getValue($user->id, 'marketing_emails', true);
    expect($value)->toBeFalse();
});

it('is idempotent — re-visiting a valid signed URL keeps marketing_emails false', function () {
    $user = User::factory()->create();
    UserSetting::setValue($user->id, 'marketing_emails', false);
    $url = URL::signedRoute('unsubscribe', ['userId' => $user->id]);

    $this->get($url)->assertOk();

    $value = UserSetting::getValue($user->id, 'marketing_emails', true);
    expect($value)->toBeFalse();
});

it('returns 403 for a tampered signature', function () {
    $user = User::factory()->create();
    $url = URL::signedRoute('unsubscribe', ['userId' => $user->id]);

    // Append extra query param to invalidate the signature
    $tamperedUrl = $url.'&tampered=1';

    $this->get($tamperedUrl)->assertForbidden();
});

it('returns 403 when the signature query param is missing', function () {
    $user = User::factory()->create();

    $this->get("/unsubscribe/{$user->id}")->assertForbidden();
});

it('returns 404 for a non-existent user with a valid signature', function () {
    $nonExistentId = 999999;
    $url = URL::signedRoute('unsubscribe', ['userId' => $nonExistentId]);

    $this->get($url)->assertNotFound();
});
