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
            $hsts = 'max-age=31536000; includeSubDomains';
            if (config('security.hsts_preload')) {
                $hsts .= '; preload';
            }
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        if (config('security.csp.enabled')) {
            $this->addCspHeader($response);
        }

        if (auth()->check()) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    private function addCspHeader(Response $response): void
    {
        $nonce = Vite::cspNonce();

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'".$this->analyticsScriptSources().$this->billingScriptSources(),
            // unsafe-inline required: Tailwind JIT + Radix UI inject runtime inline styles.
            // Nonce-based styles would require ejecting from Tailwind's JIT engine.
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' https://fonts.bunny.net",
            "connect-src 'self'".$this->connectSources(),
            'frame-src '.(config('features.billing.enabled') ? "'self'".$this->frameSources() : "'none'"),
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

    /**
     * Only called when billing is enabled (outer ternary in addCspHeader is the authoritative gate).
     * https://js.stripe.com   – Stripe Elements / Payment Element iframes
     * https://hooks.stripe.com – 3DS2 challenge frames (SCA authentication flow)
     */
    private function frameSources(): string
    {
        return ' https://js.stripe.com https://hooks.stripe.com';
    }

    private function billingScriptSources(): string
    {
        if (config('features.billing.enabled')) {
            return ' https://js.stripe.com';
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

        if (config('features.billing.enabled')) {
            // api.stripe.com: Stripe.js API calls
            // m.stripe.com / m.stripe.network: Stripe Radar fraud-signal telemetry
            $extra .= ' https://api.stripe.com https://m.stripe.com https://m.stripe.network';
        }

        return $extra;
    }
}
