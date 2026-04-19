<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class IndexNowKeyFileController extends Controller
{
    /**
     * Serve the IndexNow verification file at /{key}.txt.
     *
     * Search engines fetch this file to prove that whoever submits
     * URLs for this host also controls the host. The file must
     * contain ONLY the configured key, with text/plain Content-Type.
     */
    public function __invoke(string $key): Response
    {
        if (! config('features.indexnow.enabled', false)) {
            abort(404);
        }

        $configured = (string) config('indexnow.key');

        // Defense in depth: route regex enforces {8,128} chars, but guard
        // against misconfigured keys (empty, whitespace-only, or too short).
        if (trim($configured) === '' || strlen($configured) < 8 || ! hash_equals($configured, $key)) {
            abort(404);
        }

        return response($configured, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
