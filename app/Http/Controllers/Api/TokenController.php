<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateTokenRequest;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group API Tokens
 *
 * Manage personal access tokens for API authentication.
 */
class TokenController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    /**
     * List tokens
     *
     * Get all API tokens for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 [{"id":1,"name":"My Token","abilities":["*"],"last_used_at":null,"expires_at":null,"created_at":"2026-01-01T00:00:00.000000Z"}]
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'tokenable_id', 'tokenable_type', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at'])
            ->orderByDesc('created_at')
            ->take(config('pagination.api.tokens', 50))
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'expires_at' => $token->expires_at?->toISOString(),
                'created_at' => $token->created_at->toISOString(),
            ]);

        return response()->json($tokens);
    }

    /**
     * Create token
     *
     * Create a new personal access token.
     *
     * @authenticated
     *
     * @response 200 {"token":"1|abc123...","id":1}
     */
    public function store(CreateTokenRequest $request): JsonResponse
    {
        $expiresAt = $request->validated('expires_at')
            ? new DateTimeImmutable($request->validated('expires_at'))
            : null;

        $token = $request->user()->createToken(
            $request->validated('name'),
            $request->validated('abilities', ['*']),
            $expiresAt
        );

        $this->auditService->log('api_token.created', [
            'token_name' => $request->validated('name'),
        ]);

        $this->cacheManager->invalidateTokens();

        return response()->json([
            'token' => $token->plainTextToken,
            'id' => $token->accessToken->id,
        ]);
    }

    /**
     * Delete token
     *
     * Revoke and delete a personal access token.
     *
     * @authenticated
     *
     * @urlParam tokenId integer required The token ID. Example: 1
     *
     * @response 200 {"success":true}
     * @response 404 {"message":"Token not found."}
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Token not found.'], 404);
        }

        $this->auditService->log('api_token.deleted', [
            'token_id' => $tokenId,
        ]);

        $this->cacheManager->invalidateTokens();

        return response()->json(['success' => true]);
    }
}
