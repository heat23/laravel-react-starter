<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Token Controller
 *
 * Manages user API tokens via Laravel Sanctum.
 * Only active when FEATURE_API_TOKENS=true in environment.
 *
 * Note: Basic token routes are also defined in routes/api.php.
 * This controller provides a more complete implementation for
 * Inertia-based pages if needed.
 */
class ApiTokenController extends Controller
{
    /**
     * List all tokens for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        // Verify feature is enabled
        if (! config('features.api_tokens.enabled', true)) {
            abort(404, 'API tokens feature is not enabled.');
        }

        $tokens = $request->user()->tokens()
            ->select(['id', 'tokenable_id', 'tokenable_type', 'name', 'abilities', 'last_used_at', 'created_at'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toISOString(),
                    'created_at' => $token->created_at->toISOString(),
                ];
            });

        return response()->json([
            'tokens' => $tokens,
        ]);
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request): JsonResponse
    {
        // Verify feature is enabled
        if (! config('features.api_tokens.enabled', true)) {
            abort(404, 'API tokens feature is not enabled.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['array'],
            'abilities.*' => ['string'],
        ]);

        // Check token limit (optional - configure via plans.php)
        $user = $request->user();
        $maxTokens = config('features.api_tokens.max_tokens_free', 1);
        $currentTokenCount = $user->tokens()->count();

        if ($currentTokenCount >= $maxTokens) {
            return response()->json([
                'message' => "You have reached the maximum number of API tokens ({$maxTokens}).",
            ], 422);
        }

        $token = $user->createToken(
            $request->name,
            $request->abilities ?? ['*']
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'id' => $token->accessToken->id,
            'message' => 'Token created successfully. Copy it now - you won\'t be able to see it again.',
        ], 201);
    }

    /**
     * Revoke an API token.
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        // Verify feature is enabled
        if (! config('features.api_tokens.enabled', true)) {
            abort(404, 'API tokens feature is not enabled.');
        }

        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Token not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Token revoked successfully.',
        ]);
    }
}
