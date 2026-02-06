<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('security.csp.enabled')) {
            Vite::useCspNonce();
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if (config('security.csp.enabled')) {
            $this->addCspHeader($response);
        }

        return $response;
    }

    private function addCspHeader(Response $response): void
    {
        $nonce = Vite::cspNonce();

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'".$this->analyticsScriptSources(),
            // unsafe-inline required: Tailwind JIT + Radix UI inject runtime inline styles.
            // Nonce-based styles would require ejecting from Tailwind's JIT engine.
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' https://fonts.bunny.net",
            "connect-src 'self'".$this->connectSources(),
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        $reportUri = config('security.csp.report_uri');
        if ($reportUri) {
            $directives[] = "report-uri {$reportUri}";
        }

        $policy = implode('; ', $directives);
        $headerName = config('security.csp.report_only')
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $response->headers->set($headerName, $policy);
    }

    private function analyticsScriptSources(): string
    {
        if (app()->isProduction() && config('services.google.analytics_id')) {
            return ' https://www.googletagmanager.com https://www.google-analytics.com';
        }

        return '';
    }

    private function connectSources(): string
    {
        $extra = '';

        if (app()->isLocal()) {
            $extra .= ' ws://localhost:* http://localhost:*';
        }

        if (app()->isProduction() && config('services.google.analytics_id')) {
            $extra .= ' https://www.google-analytics.com';
        }

        return $extra;
    }
}
