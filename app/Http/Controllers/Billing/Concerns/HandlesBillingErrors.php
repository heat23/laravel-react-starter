<?php

namespace App\Http\Controllers\Billing\Concerns;

use App\Services\CacheInvalidationManager;
use Stripe\Exception\ApiErrorException;

trait HandlesBillingErrors
{
    private function invalidateAdminCaches(): void
    {
        app(CacheInvalidationManager::class)->invalidateBilling();
    }

    private function friendlyStripeError(ApiErrorException $e): string
    {
        $code = $e->getStripeCode();

        return match ($code) {
            'card_declined' => 'Your card was declined. Please try a different payment method.',
            'expired_card' => 'Your card has expired. Please update your payment method.',
            'processing_error' => 'There was an error processing your card. Please try again in a few minutes.',
            'incorrect_cvc' => 'The CVC number is incorrect. Please check and try again.',
            'insufficient_funds' => 'Insufficient funds. Please try a different payment method.',
            default => 'Unable to process your request. Please try again or contact support.',
        };
    }
}
