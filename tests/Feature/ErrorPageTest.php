<?php

use Illuminate\Support\Facades\Route;

test('404 returns inertia error component', function () {
    $response = $this->get('/this-route-does-not-exist');

    $response->assertStatus(404);
    $response->assertInertia(fn ($page) => $page
        ->component('Error')
        ->where('status', 404)
    );
});

test('403 returns inertia error component', function () {
    Route::middleware('web')->get('/test-403-abort', fn () => abort(403));

    $response = $this->get('/test-403-abort');

    $response->assertStatus(403);
    $response->assertInertia(fn ($page) => $page
        ->component('Error')
        ->where('status', 403)
    );
});

test('api routes return json not inertia', function () {
    $response = $this->getJson('/api/nonexistent-endpoint');

    $response->assertStatus(404);
    $response->assertJsonStructure(['message']);
});
