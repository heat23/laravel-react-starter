<?php

namespace App\Http\Middleware;

use App\Webhooks\Contracts\WebhookProvider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $providerKey = $request->route('provider');
        $config = config("webhooks.incoming.providers.{$providerKey}");

        if (! $config || empty($config['class'])) {
            abort(403);
        }

        /** @var WebhookProvider $provider */
        $provider = app($config['class']);
        $rawPayload = $request->getContent();

        if (! $provider->verify($request, $rawPayload)) {
            abort(403);
        }

        $request->attributes->set('webhook_provider', $provider);

        return $next($request);
    }
}
