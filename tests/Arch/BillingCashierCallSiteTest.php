<?php

/**
 * Architectural guard: Cashier subscription mutation methods must only be called
 * from app/Services/BillingService.php.
 *
 * Direct Cashier calls in controllers or other services bypass the Redis-lock
 * protection in BillingService, creating race conditions. After Sitting 3, all
 * Cashier mutation call sites must live in BillingService.
 *
 * Banned methods (called directly on Subscription): cancel, cancelNow,
 * cancelNowAndInvoice, resume, swap, swapAndInvoice, updateQuantity,
 * incrementQuantity, decrementQuantity, noProrate, anchorBillingCycleOn,
 * applyCoupon, updateStripeSubscription.
 *
 * @group arch
 */
it('Cashier mutation methods are only called from BillingService', function () {
    // Methods that are unique enough to Cashier subscriptions that a grep
    // is reliable. More generic names (cancel, resume) are excluded from
    // the grep because they appear legitimately on other objects; the
    // truly Cashier-specific set is sufficient to guard the contract.
    $cashierSpecificMethods = [
        'cancelNow',
        'cancelNowAndInvoice',
        'swapAndInvoice',
        'applyCoupon',
        'noProrate',
        'anchorBillingCycleOn',
        'updateStripeSubscription',
        'incrementQuantity',
        'decrementQuantity',
    ];

    $violations = [];

    $appPhpFiles = glob(base_path('app').'/**/*.php') ?: [];
    // Also scan one level deeper
    $appPhpFiles = array_merge(
        $appPhpFiles,
        glob(base_path('app').'/**/**/*.php') ?: [],
        glob(base_path('app').'/**/**/**/*.php') ?: [],
    );
    $appPhpFiles = array_unique($appPhpFiles);

    $billingServicePath = base_path('app/Services/BillingService.php');

    foreach ($appPhpFiles as $file) {
        if ($file === $billingServicePath) {
            continue;
        }

        $content = file_get_contents($file);
        $relativePath = str_replace(base_path('/'), '', $file);

        foreach ($cashierSpecificMethods as $method) {
            if (str_contains($content, "->{$method}(")) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                foreach ($lines as $lineNo => $line) {
                    if (str_contains($line, "->{$method}(")) {
                        $trimmed = ltrim($line);
                        // Skip comment lines
                        if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#')) {
                            continue;
                        }
                        $violations[] = "{$relativePath}:".($lineNo + 1)." — {$method}() called outside BillingService";
                    }
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Cashier mutation methods found outside BillingService. Move to BillingService with Redis lock:\n".
        implode("\n", $violations)
    );
});
