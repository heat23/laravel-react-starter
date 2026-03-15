<?php

use App\Models\AuditLog;
use App\Models\User;

it('requires authentication to submit feedback', function () {
    $response = $this->postJson('/feedback', [
        'type' => 'bug',
        'message' => 'Something is broken.',
    ]);

    $response->assertStatus(401);
});

it('stores feedback in audit log', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/feedback', [
        'type' => 'feature',
        'message' => 'Please add dark mode for the dashboard.',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'feedback.submitted',
        'user_id' => $user->id,
    ]);

    $log = AuditLog::where('event', 'feedback.submitted')->first();
    expect($log->metadata)->toMatchArray([
        'type' => 'feature',
        'message' => 'Please add dark mode for the dashboard.',
    ]);
});

it('validates required fields for feedback', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/feedback', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['type', 'message']);
});

it('validates feedback type is valid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/feedback', [
        'type' => 'invalid-type',
        'message' => 'Some message.',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['type']);
});

it('validates feedback message max length', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/feedback', [
        'type' => 'bug',
        'message' => str_repeat('a', 2001),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});
