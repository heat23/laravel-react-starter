<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserSetting;

it('exports all personal data categories as JSON', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    UserSetting::setValue($user->id, 'timezone', 'America/New_York');

    AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $user->id,
        'ip' => '127.0.0.1',
        'user_agent' => 'TestAgent',
        'metadata' => ['source' => 'web'],
    ]);

    $user->createToken('test-token', ['read']);

    $response = $this->actingAs($user)->get('/export/personal-data');

    $response->assertOk();
    $response->assertHeader('content-disposition', 'attachment; filename="personal-data-export.json"');

    $data = $response->json();

    expect($data)->toHaveKey('exported_at');
    expect($data)->toHaveKey('user');
    expect($data)->toHaveKey('settings');
    expect($data)->toHaveKey('audit_logs');
    expect($data)->toHaveKey('api_tokens');
    expect($data)->toHaveKey('social_accounts');
    expect($data)->toHaveKey('webhook_endpoints');

    expect($data['user']['name'])->toBe($user->name);
    expect($data['user']['email'])->toBe($user->email);

    $settingKeys = collect($data['settings'])->pluck('key')->all();
    expect($settingKeys)->toContain('timezone');

    expect($data['audit_logs'])->toHaveCount(1);
    expect($data['audit_logs'][0]['event'])->toBe('auth.login');

    expect($data['api_tokens'])->toHaveCount(1);
    expect($data['api_tokens'][0]['name'])->toBe('test-token');
});

it('excludes sensitive fields from export', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $user->createToken('test-token', ['read']);

    $response = $this->actingAs($user)->get('/export/personal-data');

    $data = $response->json();

    expect($data['user'])->not->toHaveKey('password');
    expect($data['user'])->not->toHaveKey('remember_token');

    foreach ($data['api_tokens'] as $token) {
        expect($token)->not->toHaveKey('token');
    }
});

it('returns 401 for unauthenticated user', function () {
    $response = $this->get('/export/personal-data');

    $response->assertRedirect(route('login'));
});

it('requires email verification to export', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/export/personal-data');

    $response->assertRedirect(route('verification.notice'));
});

it('only exports data for the authenticated user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $otherUser = User::factory()->create();

    AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $otherUser->id,
        'ip' => '127.0.0.1',
        'metadata' => [],
    ]);

    $response = $this->actingAs($user)->get('/export/personal-data');

    $data = $response->json();
    expect($data['audit_logs'])->toHaveCount(0);
});
