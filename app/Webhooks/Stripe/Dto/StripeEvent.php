<?php

namespace App\Webhooks\Stripe\Dto;

readonly class StripeEvent
{
    public function __construct(public array $payload) {}
}
