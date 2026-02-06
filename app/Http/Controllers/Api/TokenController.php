<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateTokenRequest;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'tokenable_id', 'tokenable_type', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at'])
            ->orderByDesc('created_at')
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

        return response()->json([
            'token' => $token->plainTextToken,
            'id' => $token->accessToken->id,
        ]);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Token not found.'], 404);
        }

        return response()->json(['success' => true]);
    }
}
