<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin/system')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/system')->assertStatus(403);
});

it('returns 403 for admin without super_admin', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);

    $this->actingAs($admin)->get('/admin/system')->assertStatus(403);
});

it('loads system page with PHP and Laravel versions', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('App/Admin/System')
        ->has('system.php_version')
        ->has('system.laravel_version')
    );
});

it('loads system page with queue stats', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(fn ($page) => $page
        ->has('system.queue')
        ->has('system.queue.driver')
    );
});

it('loads system page with package list', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(fn ($page) => $page
        ->has('system.packages')
    );
});

it('includes server info', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(fn ($page) => $page
        ->has('system.server')
        ->has('system.server.os')
        ->has('system.server.server_software')
    );
});

it('includes database info', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(fn ($page) => $page
        ->has('system.database')
        ->has('system.database.driver')
    );
});

it('php version is redacted to major.minor.x', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(function ($page) {
        $phpVersion = $page->toArray()['props']['system']['php_version'];
        expect($phpVersion)->toMatch('/^\d+\.\d+\.x$/');
        expect($phpVersion)->toBe(PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.x');
    });
});

it('laravel version is redacted to major.minor.x', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(function ($page) {
        expect($page->toArray()['props']['system']['laravel_version'])->toMatch('/^\d+\.\d+\.x$/');
    });
});

it('packages include laravel framework', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(function ($page) {
        $packages = $page->toArray()['props']['system']['packages'];
        $names = array_column($packages, 'name');
        expect($names)->toContain('laravel/framework');
    });
});

it('packages have name and version', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(function ($page) {
        $packages = $page->toArray()['props']['system']['packages'];
        foreach ($packages as $pkg) {
            expect($pkg)->toHaveKey('name');
            expect($pkg)->toHaveKey('version');
        }
    });
});

it('package versions are redacted to major.minor.x', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(function ($page) {
        $packages = $page->toArray()['props']['system']['packages'];
        expect($packages)->not->toBeEmpty();
        foreach ($packages as $pkg) {
            expect($pkg['version'])->toMatch('/^\d+\.\d+\.x$/');
        }
    });
});

it('queue stats include pending and failed jobs', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get('/admin/system');

    $response->assertInertia(fn ($page) => $page
        ->has('system.queue.pending_jobs')
        ->has('system.queue.failed_jobs')
    );
});
