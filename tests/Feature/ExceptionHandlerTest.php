<?php

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Register test-only API routes that throw specific exceptions
    Route::middleware('api')->prefix('api')->group(function () {
        Route::get('/test-404-model', fn () => throw new ModelNotFoundException('User'));
        Route::get('/test-403', fn () => throw new AuthorizationException('Forbidden'));
        Route::get('/test-500', fn () => throw new \RuntimeException('Something broke'));
        Route::post('/test-validation', function (\Illuminate\Http\Request $request) {
            $request->validate(['name' => 'required|string|min:1']);

            return response()->json(['ok' => true]);
        });
    });
});

test('returns json error for api 404', function () {
    $response = $this->getJson('/api/nonexistent-route');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Not found',
            'status' => 404,
        ])
        ->assertJsonStructure(['message', 'errors', 'status']);
});

test('returns json error for api model not found', function () {
    $response = $this->getJson('/api/test-404-model');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Resource not found',
            'status' => 404,
        ]);
});

test('returns json error for api 500 without debug details in production', function () {
    config(['app.debug' => false]);

    $response = $this->getJson('/api/test-500');

    $response->assertStatus(500)
        ->assertJson([
            'message' => 'Internal server error',
            'status' => 500,
        ])
        ->assertJsonMissing(['message' => 'Something broke']);
});

test('includes debug details when app debug is true', function () {
    config(['app.debug' => true]);

    $response = $this->getJson('/api/test-500');

    $response->assertStatus(500)
        ->assertJson([
            'message' => 'Something broke',
            'status' => 500,
        ]);
});

test('returns json error for api 401', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated',
            'status' => 401,
        ]);
});

test('returns json error for api 403', function () {
    $response = $this->getJson('/api/test-403');

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Forbidden',
            'status' => 403,
        ]);
});

test('returns json error for api 429', function () {
    Route::middleware(['api', 'throttle:1,1'])->prefix('api')->group(function () {
        Route::get('/test-throttle', fn () => response()->json(['ok' => true]));
    });

    $this->getJson('/api/test-throttle');
    $response = $this->getJson('/api/test-throttle');

    $response->assertStatus(429)
        ->assertJson([
            'message' => 'Too many requests',
            'status' => 429,
        ]);
});

test('includes request id in error response header', function () {
    $response = $this->getJson('/api/nonexistent-route');

    $response->assertStatus(404)
        ->assertHeader('X-Request-Id');
});

test('does not intercept inertia requests', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
        ])
        ->get('/nonexistent-web-route');

    // Should get an Inertia page response (has 'component' key), not our JSON envelope
    $response->assertJsonStructure(['component', 'props', 'url']);
    $response->assertJsonPath('component', 'Error');
});

test('does not leak stack traces in production', function () {
    config(['app.debug' => false]);

    $response = $this->getJson('/api/test-500');

    $content = $response->getContent();
    expect($content)
        ->not->toContain('trace')
        ->not->toContain('.php')
        ->not->toContain('line');
});

test('validation errors use default laravel format', function () {
    $response = $this->postJson('/api/test-validation', [
        'name' => '',
    ]);

    // Laravel's default validation response should not be overridden
    $response->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});
