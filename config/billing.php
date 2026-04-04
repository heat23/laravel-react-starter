<?php

/**
 * Billing Configuration
 *
 * Operational thresholds for billing and subscription management.
 * Only used when the billing feature is enabled.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Redis Lock Timeout
    |--------------------------------------------------------------------------
    |
    | Duration in seconds for the distributed lock held during Stripe API
    | mutations (cancel, resume, swap, etc.). Sized to cover a full Stripe
    | round-trip (~30 s) plus a 5-second buffer.
    |
    */

    'lock_timeout' => (int) env('BILLING_LOCK_TIMEOUT', 35),

    /*
    |--------------------------------------------------------------------------
    | Past-Due Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days a past_due subscription remains on its paid tier before
    | being downgraded to free. Increase for lenient dunning; reduce for strict.
    |
    */

    'grace_period_days' => (int) env('PAST_DUE_GRACE_DAYS', 7),

];
