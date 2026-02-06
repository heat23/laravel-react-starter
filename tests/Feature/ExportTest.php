<?php

use App\Models\User;

it('exports CSV for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/export/users');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertHeader('content-disposition');
    expect($response->headers->get('content-disposition'))->toContain('users.csv');
});

it('returns 401 for unauthenticated user', function () {
    $response = $this->get('/export/users');

    $response->assertRedirect(route('login'));
});

it('applies search filter', function () {
    $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $response = $this->actingAs($user)->get('/export/users?search=john');

    $response->assertOk();
    $content = $response->streamedContent();
    expect($content)->toContain('john@example.com');
    expect($content)->not()->toContain('jane@example.com');
});

it('includes correct CSV headers in output', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/export/users');

    $content = $response->streamedContent();
    $lines = explode("\n", trim($content));
    $header = str_replace("\xEF\xBB\xBF", '', $lines[0]);
    expect($header)->toContain('Name');
    expect($header)->toContain('Email');
});
