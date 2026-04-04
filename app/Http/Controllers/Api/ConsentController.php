<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categories.necessary' => ['required', 'boolean', 'accepted'],
            'categories.analytics' => ['required', 'boolean'],
            'categories.marketing' => ['required', 'boolean'],
            'version' => ['sometimes', 'string', 'max:20', 'regex:/^[\d.]+$/'],
            'timestamp' => ['sometimes', 'string', 'max:50', 'regex:/^\d{4}-\d{2}-\d{2}T[\d:Z.+\-]+$/'],
        ]);

        // Normalise necessary to canonical bool — accepted rule allows 1/"1"/true
        $validated['categories']['necessary'] = (bool) $validated['categories']['necessary'];

        Log::info('cookie_consent_recorded', [
            'categories' => $validated['categories'],
            'version' => $validated['version'] ?? null,
            'timestamp' => $validated['timestamp'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Persist the analytics consent decision for authenticated users so that
        // AuditService can gate server-side GA4 forwarding against explicit declines.
        if ($request->user()) {
            $request->user()->setSetting(
                AuditService::ANALYTICS_CONSENT_KEY,
                (bool) ($validated['categories']['analytics'] ?? false)
            );
        }

        return response()->json(['success' => true]);
    }
}
