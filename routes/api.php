<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User settings API (for theme persistence, etc.)
if (config('features.user_settings.enabled', true)) {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/settings', function (Request $request) {
            $user = $request->user();
            return response()->json([
                'theme' => $user->getSetting('theme', 'system'),
                'timezone' => $user->getSetting('timezone', config('app.timezone')),
            ]);
        });

        Route::post('/settings', function (Request $request) {
            $request->validate([
                'key' => 'required|string|max:64',
                'value' => 'required',
            ]);

            $user = $request->user();
            $user->setSetting($request->key, $request->value);

            return response()->json(['success' => true]);
        });
    });
}

// API token management (for users managing their own tokens)
if (config('features.api_tokens.enabled', true)) {
    Route::middleware('auth:sanctum')->prefix('tokens')->group(function () {
        Route::get('/', function (Request $request) {
            return $request->user()->tokens()->get()->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                ];
            });
        });

        Route::post('/', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'abilities' => 'array',
                'abilities.*' => 'string',
            ]);

            $token = $request->user()->createToken(
                $request->name,
                $request->abilities ?? ['*']
            );

            return response()->json([
                'token' => $token->plainTextToken,
                'id' => $token->accessToken->id,
            ]);
        });

        Route::delete('/{tokenId}', function (Request $request, $tokenId) {
            $request->user()->tokens()->where('id', $tokenId)->delete();
            return response()->json(['success' => true]);
        });
    });
}
