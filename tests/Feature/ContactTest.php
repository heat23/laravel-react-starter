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
