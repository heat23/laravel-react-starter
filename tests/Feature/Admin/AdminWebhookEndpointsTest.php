<?php

use App\Models\User;
use App\Models\WebhookEndpoint;

beforeEach(function () {
    config(['features.webhooks.enabled' => true]);
    registerAdminRoutes();
    ensureWebhookTablesExist();
});

it('admin can list webhook endpoints including soft-deleted', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    WebhookEndpoint::factory()->create(['user_id' => $user->id, 'url' => 'https://active.example.com']);
    $deleted = WebhookEndpoint::factory()->create(['user_id' => $user->id, 'url' => 'https://deleted.example.com']);
    $deleted->delete();

    $response = $this->actingAs($admin)->get('/admin/webhooks/endpoints');

    $response->assertOk();
    $endpoints = $response->inertia()->prop('endpoints.data');
    $urls = collect($endpoints)->pluck('url');
    expect($urls)->toContain('https://active.example.com');
    expect($urls)->toContain('https://deleted.example.com');
});

it('admin can restore a soft-deleted webhook endpoint', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);
    $endpoint->delete();

    expect(WebhookEndpoint::find($endpoint->id))->toBeNull();

    $this->actingAs($admin)
        ->patch("/admin/webhooks/endpoints/{$endpoint->id}/restore")
        ->assertRedirect('/admin/webhooks/endpoints');

    expect(WebhookEndpoint::withTrashed()->find($endpoint->id)->deleted_at)->toBeNull();
});

it('regular admin cannot restore endpoints', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);
    $endpoint->delete();

    $this->actingAs($admin)
        ->patch("/admin/webhooks/endpoints/{$endpoint->id}/restore")
        ->assertForbidden();
});

it('endpoints list renders correct inertia component', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/webhooks/endpoints')
        ->assertInertia(fn ($page) => $page->component('Admin/Webhooks/Endpoints'));
});

it('non-deleted endpoint returns 422 when trying to restore', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()->create(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->patch("/admin/webhooks/endpoints/{$endpoint->id}/restore")
        ->assertStatus(422);
});
