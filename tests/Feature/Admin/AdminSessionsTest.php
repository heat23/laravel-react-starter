<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

it('admin can view the sessions page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/sessions')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Sessions/Index'));
});

it('admin can terminate user sessions', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/sessions/{$user->id}")
        ->assertRedirect('/admin/sessions');
});

it('regular admin cannot terminate sessions', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/sessions/{$user->id}")
        ->assertForbidden();
});

it('escapes LIKE wildcards in session search', function () {
    Config::set('session.driver', 'database');

    $admin = User::factory()->admin()->create();
    $wildcardUser = User::factory()->create(['name' => 'test%user', 'email' => 'wildcard@example.com']);
    $normalUser = User::factory()->create(['name' => 'testNormal', 'email' => 'normal@example.com']);

    DB::table('sessions')->insert([
        ['id' => 'sess-wildcard', 'user_id' => $wildcardUser->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => '', 'last_activity' => time()],
        ['id' => 'sess-normal', 'user_id' => $normalUser->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => '', 'last_activity' => time()],
    ]);

    // Literal % in search should match only the user whose name contains "%", not all "test*" users
    $response = $this->actingAs($admin)->get('/admin/sessions?search=test%25user');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('sessions.data', 1)
            ->where('sessions.data.0.user_id', $wildcardUser->id)
        );
});
