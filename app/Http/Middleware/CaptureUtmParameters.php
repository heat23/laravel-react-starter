<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    private const UTM_PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    /**
     * Capture UTM parameters from GET requests and store them in the session (first-touch only).
     * Stored under session key 'utm_data' as an associative array.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            $utm = [];

            foreach (self::UTM_PARAMS as $param) {
                $value = $request->query($param);
                if ($value !== null && $value !== '') {
                    $utm[$param] = (string) $value;
                }
            }

            // First-touch attribution: only store if no UTM data is already in session
            if (! empty($utm) && ! $request->session()->has('utm_data')) {
                $request->session()->put('utm_data', $utm);
            }
        }

        return $next($request);
    }
}
