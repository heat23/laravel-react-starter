<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = $request->route('provider');
        $config = config("webhooks.incoming.providers.{$provider}");

        if (! $config || ! $config['secret']) {
            Log::warning('Webhook received for unconfigured provider', ['provider' => $provider]);
            abort(403);
        }

        $signatureHeader = $request->header($config['signature_header']);

        if (! $signatureHeader) {
            abort(403);
        }

        $payload = $request->getContent();

        // Handle different signature formats
        $actualSignature = $this->extractSignature($signatureHeader, $provider);

        // Stripe signs "timestamp.payload"; other providers sign payload only
        $signedContent = $payload;
        $timestamp = $this->extractTimestamp($request, $provider);

        if ($provider === 'stripe' && $timestamp !== null) {
            $signedContent = $timestamp.'.'.$payload;
        }

        $expectedSignature = hash_hmac($config['algorithm'], $signedContent, $config['secret']);

        if (! hash_equals($expectedSignature, $actualSignature)) {
            abort(403);
        }

        // Replay protection
        if ($timestamp) {
            $tolerance = config('webhooks.incoming.replay_tolerance', 300);
            if (abs(time() - $timestamp) > $tolerance) {
                abort(403);
            }
        }

        return $next($request);
    }

    private function extractSignature(string $header, string $provider): string
    {
        return match ($provider) {
            'github' => str_replace('sha256=', '', $header),
            'stripe' => $this->extractStripeSignature($header),
            default => $header,
        };
    }

    private function extractStripeSignature(string $header): string
    {
        $parts = collect(explode(',', $header))
            ->mapWithKeys(function ($part) {
                [$key, $value] = explode('=', trim($part), 2);

                return [$key => $value];
            });

        return $parts->get('v1', '');
    }

    private function extractTimestamp(Request $request, string $provider): ?int
    {
        return match ($provider) {
            'stripe' => $this->extractStripeTimestamp($request->header('Stripe-Signature', '')),
            default => null,
        };
    }

    private function extractStripeTimestamp(string $header): ?int
    {
        $parts = collect(explode(',', $header))
            ->mapWithKeys(function ($part) {
                [$key, $value] = explode('=', trim($part), 2);

                return [$key => $value];
            });

        $timestamp = $parts->get('t');

        return $timestamp ? (int) $timestamp : null;
    }
}
