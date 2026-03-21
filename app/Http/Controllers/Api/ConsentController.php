<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categories.necessary' => ['required', 'boolean'],
            'categories.analytics' => ['required', 'boolean'],
            'categories.marketing' => ['required', 'boolean'],
            'version' => ['sometimes', 'string', 'max:20', 'regex:/^[\d.]+$/'],
            'timestamp' => ['sometimes', 'string', 'max:50', 'regex:/^\d{4}-\d{2}-\d{2}T[\d:Z.+\-]+$/'],
        ]);

        Log::info('cookie_consent_recorded', [
            'categories' => $validated['categories'],
            'version' => $validated['version'] ?? null,
            'timestamp' => $validated['timestamp'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true]);
    }
}
