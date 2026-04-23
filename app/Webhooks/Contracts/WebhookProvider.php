<?php

namespace App\Webhooks\Contracts;

use App\Webhooks\Dto\IncomingWebhookEvent;
use Illuminate\Http\Request;

interface WebhookProvider
{
    public function name(): string;

    public function verify(Request $request, string $rawPayload): bool;

    public function parseEvent(string $rawPayload, array $headers): IncomingWebhookEvent;
}
