<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['features.webhooks.enabled' => true]);
    registerAdminRoutes();
    ensureWebhookTablesExist();
});

function ensureWebhookTablesExist(): void
{
    if (! Schema::hasTable('webhook_endpoints')) {
        Schema::create('webhook_endpoints', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('url');
            $table->json('events');
            $table->text('secret');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    if (! Schema::hasTable('webhook_deliveries')) {
        Schema::create('webhook_deliveries', function ($table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id');
            $table->uuid('uuid')->unique();
            $table->string('event_type');
            $table->json('payload');
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('incoming_webhooks')) {
        Schema::create('incoming_webhooks', function ($table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('external_id')->nullable();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('received');
            $table->timestamps();
        });
    }
}

it('redirects guests to login', function () {
    $this->get('/admin/webhooks')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/webhooks')->assertStatus(403);
});

it('loads webhooks dashboard with stats', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/webhooks');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Webhooks/Dashboard')
        ->has('stats')
        ->where('stats.total_endpoints', 0)
        ->where('stats.total_deliveries', 0)
        ->where('stats.failure_rate', 0)
        ->has('delivery_chart')
        ->has('recent_failures')
    );
});

it('counts webhook endpoints and deliveries', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    DB::table('webhook_endpoints')->insert([
        'user_id' => $user->id,
        'url' => 'https://example.com/hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/webhooks');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_endpoints', 1)
        ->where('stats.active_endpoints', 1)
    );
});

it('loads incoming webhooks index', function () {
    $admin = User::factory()->admin()->create();

    DB::table('incoming_webhooks')->insert([
        'provider' => 'github',
        'event_type' => 'push',
        'payload' => json_encode(['ref' => 'refs/heads/main']),
        'status' => 'received',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/admin/webhooks/incoming');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Webhooks/IncomingWebhooks')
        ->has('webhooks.data', 1)
        ->where('webhooks.data.0.provider', 'github')
        ->has('providers')
        ->has('filters')
    );
});

it('filters incoming webhooks by provider', function () {
    $admin = User::factory()->admin()->create();

    DB::table('incoming_webhooks')->insert([
        ['provider' => 'github', 'event_type' => 'push', 'payload' => json_encode([]), 'status' => 'received', 'created_at' => now(), 'updated_at' => now()],
        ['provider' => 'stripe', 'event_type' => 'charge.succeeded', 'payload' => json_encode([]), 'status' => 'processed', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->actingAs($admin)->get('/admin/webhooks/incoming?provider=github');

    $response->assertInertia(fn ($page) => $page
        ->has('webhooks.data', 1)
        ->where('webhooks.data.0.provider', 'github')
        ->where('filters.provider', 'github')
    );
});

it('filters incoming webhooks by status', function () {
    $admin = User::factory()->admin()->create();

    DB::table('incoming_webhooks')->insert([
        ['provider' => 'github', 'event_type' => 'push', 'payload' => json_encode([]), 'status' => 'received', 'created_at' => now(), 'updated_at' => now()],
        ['provider' => 'stripe', 'event_type' => 'charge.succeeded', 'payload' => json_encode([]), 'status' => 'processed', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->actingAs($admin)->get('/admin/webhooks/incoming?status=processed');

    $response->assertInertia(fn ($page) => $page
        ->has('webhooks.data', 1)
        ->where('webhooks.data.0.status', 'processed')
    );
});

it('rejects non-admin access to incoming webhooks', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/webhooks/incoming')->assertStatus(403);
});

it('restores a soft-deleted webhook endpoint as super_admin', function () {
    $superAdmin = User::factory()->superAdmin()->admin()->create();
    $owner = User::factory()->create();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $owner->id,
        'url' => 'https://example.com/deleted-hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'deleted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($superAdmin)->patch("/admin/webhooks/endpoints/{$endpointId}/restore");

    $response->assertRedirect(route('admin.webhooks.endpoints'));
    $response->assertSessionHas('success');
    expect(DB::table('webhook_endpoints')->where('id', $endpointId)->whereNull('deleted_at')->exists())->toBeTrue();
});

it('returns 403 when non-super_admin tries to restore endpoint', function () {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $owner->id,
        'url' => 'https://example.com/deleted-hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'deleted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)->patch("/admin/webhooks/endpoints/{$endpointId}/restore")->assertStatus(403);
});

it('returns 422 when trying to restore a non-deleted endpoint', function () {
    $superAdmin = User::factory()->superAdmin()->admin()->create();
    $owner = User::factory()->create();

    $endpointId = DB::table('webhook_endpoints')->insertGetId([
        'user_id' => $owner->id,
        'url' => 'https://example.com/active-hook',
        'events' => json_encode(['user.created']),
        'secret' => encrypt('test_secret'),
        'active' => true,
        'deleted_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($superAdmin)->patch("/admin/webhooks/endpoints/{$endpointId}/restore")->assertStatus(422);
});
