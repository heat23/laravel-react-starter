<?php

namespace App\Webhooks\Stripe\Contracts;

use App\Webhooks\Stripe\Dto\StripeEvent;

interface StripeEventHandler
{
    public function handle(StripeEvent $event): void;
}
