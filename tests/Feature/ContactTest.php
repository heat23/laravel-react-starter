<?php

use App\Models\AuditLog;
use App\Models\User;

it('renders contact page', function () {
    $response = $this->get('/contact');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Contact'));
});

it('submits contact form and creates audit log', function () {
    $response = $this->post('/contact', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Enterprise pricing',
        'message' => 'I would like to learn about enterprise plans.',
    ]);

    $response->assertRedirect('/contact');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'contact.submitted',
    ]);

    $log = AuditLog::where('event', 'contact.submitted')->first();
    expect($log->metadata)->toMatchArray([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Enterprise pricing',
    ]);
});

it('validates required fields on contact form', function () {
    $response = $this->post('/contact', []);

    $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});

it('validates email format on contact form', function () {
    $response = $this->post('/contact', [
        'name' => 'Jane',
        'email' => 'not-an-email',
        'subject' => 'General inquiry',
        'message' => 'Hello',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('validates message max length on contact form', function () {
    $response = $this->post('/contact', [
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'subject' => 'General inquiry',
        'message' => str_repeat('a', 2001),
    ]);

    $response->assertSessionHasErrors(['message']);
});

it('allows authenticated users to submit contact form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/contact', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Bug report',
        'message' => 'Found an issue.',
    ]);

    $response->assertRedirect('/contact');
    $response->assertSessionHas('success');
});

// contact.sales tests

it('submits sales inquiry and stores submission', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'John Smith',
        'email' => 'john@acme.com',
        'company' => 'Acme Inc',
        'seats_needed' => 20,
        'message' => 'We need enterprise pricing for our team.',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'John Smith',
        'email' => 'john@acme.com',
        'subject' => 'Enterprise pricing',
        'status' => 'new',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'contact.submitted',
    ]);
});

it('validates required fields on sales inquiry form', function () {
    $response = $this->post('/contact/sales', []);

    $response->assertSessionHasErrors(['name', 'email', 'company', 'seats_needed', 'message']);
});

it('validates email format on sales inquiry form', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'John',
        'email' => 'not-an-email',
        'company' => 'Acme',
        'seats_needed' => 5,
        'message' => 'Hello',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('validates seats_needed must be a positive integer on sales form', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'John',
        'email' => 'john@acme.com',
        'company' => 'Acme',
        'seats_needed' => 0,
        'message' => 'Hello',
    ]);

    $response->assertSessionHasErrors(['seats_needed']);
});

it('validates message max length on sales form', function () {
    $response = $this->post('/contact/sales', [
        'name' => 'John',
        'email' => 'john@acme.com',
        'company' => 'Acme',
        'seats_needed' => 10,
        'message' => str_repeat('a', 2001),
    ]);

    $response->assertSessionHasErrors(['message']);
});

it('allows authenticated users to submit sales inquiry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/contact/sales', [
        'name' => 'Jane Doe',
        'email' => 'jane@acme.com',
        'company' => 'Acme Corp',
        'seats_needed' => 15,
        'message' => 'Looking for enterprise pricing.',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('contact_submissions', [
        'email' => 'jane@acme.com',
        'subject' => 'Enterprise pricing',
    ]);
});
