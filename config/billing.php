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

    /*
    |--------------------------------------------------------------------------
    | Legacy Subscribe Endpoint Kill Switch
    |--------------------------------------------------------------------------
    |
    | The POST /billing/subscribe endpoint is deprecated in favour of the
    | Stripe Hosted Checkout flow (POST /billing/checkout). Set this to false
    | to return HTTP 410 Gone for any callers that have not yet migrated.
    | Defaults to true so existing integrations are not broken on deploy.
    |
    */

    'legacy_subscribe_enabled' => (bool) env('BILLING_LEGACY_SUBSCRIBE', true),

];
