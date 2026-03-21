<?php

namespace App\Http\Controllers;

use App\Models\NpsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NpsSurveyController extends Controller
{
    /**
     * Check if the authenticated user is eligible to see the NPS survey.
     * Eligible: verified email, account 7+ days old, never responded OR last response 90+ days ago.
     */
    public function eligible(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->hasVerifiedEmail()) {
            return response()->json(['eligible' => false]);
        }

        // Must be at least 7 days old
        if ($user->created_at->diffInDays(now()) < 7) {
            return response()->json(['eligible' => false]);
        }

        $lastResponse = NpsResponse::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->value('created_at');

        if ($lastResponse && now()->diffInDays($lastResponse) < 90) {
            return response()->json(['eligible' => false]);
        }

        return response()->json(['eligible' => true]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:10'],
            'comment' => ['nullable', 'string', 'max:500'],
            'survey_trigger' => ['nullable', 'string', 'in:post_onboarding,quarterly'],
        ]);

        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        // Cooldown: reject if submitted within 90 days
        $lastResponse = NpsResponse::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->value('created_at');

        if ($lastResponse && now()->diffInDays($lastResponse) < 90) {
            return response()->json(['error' => 'Survey cooldown active'], 422);
        }

        NpsResponse::create([
            'user_id' => $user->id,
            'score' => $validated['score'],
            'comment' => $validated['comment'] ?? null,
            'survey_trigger' => $validated['survey_trigger'] ?? 'post_onboarding',
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
