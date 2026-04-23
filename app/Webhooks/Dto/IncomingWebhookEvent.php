<?php

namespace App\Webhooks\Dto;

readonly class IncomingWebhookEvent
{
    public function __construct(
        public string $provider,
        public string $eventType,
        public string $externalId,
        public array $payload,
    ) {}
}
