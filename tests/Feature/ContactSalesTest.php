<?php

use App\Models\AuditLog;
use App\Models\ContactSubmission;

it('submits sales inquiry and creates contact submission', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'company' => 'Acme Corp',
        'seats_needed' => 25,
        'message' => 'Looking for enterprise plan details.',
    ]);

    $response->assertRedirect(route('pricing'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'subject' => 'Enterprise pricing',
        'status' => 'new',
    ]);
});

it('submits sales inquiry without optional message', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'Bob Builder',
        'email' => 'bob@builder.com',
        'company' => 'Builder Inc',
        'seats_needed' => 10,
    ]);

    $response->assertRedirect(route('pricing'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('contact_submissions', [
        'email' => 'bob@builder.com',
        'subject' => 'Enterprise pricing',
    ]);
});

it('creates audit log on sales inquiry submission', function () {
    $this->post('/contact/sales', [
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'company' => 'Acme Corp',
        'seats_needed' => 25,
    ]);

    $log = AuditLog::where('event', 'contact.submitted')->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->metadata)->toMatchArray([
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'company' => 'Acme Corp',
        'seats_needed' => 25,
    ]);
});

it('validates required fields on sales inquiry form', function () {
    $response = $this->post('/contact/sales', []);

    $response->assertSessionHasErrors(['name', 'email', 'company', 'seats_needed']);
});

it('validates email format on sales inquiry', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'Jane Smith',
        'email' => 'not-an-email',
        'company' => 'Acme Corp',
        'seats_needed' => 25,
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('validates seats_needed must be a positive integer', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'company' => 'Acme Corp',
        'seats_needed' => 0,
    ]);

    $response->assertSessionHasErrors(['seats_needed']);
});

it('validates message max length on sales inquiry', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'company' => 'Acme Corp',
        'seats_needed' => 25,
        'message' => str_repeat('A', 2001),
    ]);

    $response->assertSessionHasErrors(['message']);
});

it('rate limits sales inquiry submissions', function () {
    // Exhaust the throttle limit (3 per minute)
    for ($i = 0; $i < 3; $i++) {
        $this->post('/contact/sales', [
            'name' => 'Jane Smith',
            'email' => 'jane@acmecorp.com',
            'company' => 'Acme Corp',
            'seats_needed' => 25,
        ]);
    }

    $response = $this->post('/contact/sales', [
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'company' => 'Acme Corp',
        'seats_needed' => 25,
    ]);

    $response->assertStatus(429);
});

it('includes company and seats in stored message', function () {
    $this->post('/contact/sales', [
        'name' => 'Jane Smith',
        'email' => 'jane@acmecorp.com',
        'company' => 'Acme Corp',
        'seats_needed' => 50,
        'message' => 'Need custom contract.',
    ]);

    $submission = ContactSubmission::where('email', 'jane@acmecorp.com')->first();
    expect($submission->message)->toContain('Company: Acme Corp');
    expect($submission->message)->toContain('Seats needed: 50');
    expect($submission->message)->toContain('Additional notes: Need custom contract.');
});
