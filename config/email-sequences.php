<?php

/**
 * Email Sequence Schedules
 *
 * Centralised timing configuration for all lifecycle email sequences.
 * Edit these values to adjust send windows without touching command logic.
 *
 * "days" / "max_days" fields are relative to the user's created_at (or relevant
 * event timestamp). A user created N days ago receives email X when:
 *   created_at <= now() - days   AND   created_at > now() - max_days
 *
 * "trial_nudge" windows are relative to trial_ends_at (positive = future,
 * negative = past). Email Y fires when:
 *   trial_ends_at BETWEEN now() + window_start AND now() + window_end
 *
 * "trial_ending.days_before_expiry" is the look-ahead window used by
 * SendTrialEndingReminders to catch upcoming trial expirations.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Welcome Sequence
    |--------------------------------------------------------------------------
    | Email 1 is sent by the Registered event listener; this config governs
    | the follow-up emails dispatched by the lifecycle:send-welcome command.
    */
    'welcome_sequence' => [
        2 => ['days' => 1, 'max_days' => 2],
        3 => ['days' => 3, 'max_days' => 5],
    ],

    /*
    |--------------------------------------------------------------------------
    | Onboarding Reminders
    |--------------------------------------------------------------------------
    | Sent to users who have not yet completed the onboarding wizard (or the
    | equivalent first-run flow when the wizard UI is disabled).
    */
    'onboarding' => [
        1 => ['days' => 1, 'max_days' => 2],
        2 => ['days' => 3, 'max_days' => 5],
        3 => ['days' => 7, 'max_days' => 10],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dunning Reminders  (billing.enabled required)
    |--------------------------------------------------------------------------
    | Sent to users with past_due subscriptions, based on days since the
    | subscription entered past_due status.
    */
    'dunning' => [
        1 => ['days' => 3,  'max_days' => 5],
        2 => ['days' => 7,  'max_days' => 10],
        3 => ['days' => 12, 'max_days' => 15],
    ],

    /*
    |--------------------------------------------------------------------------
    | Win-Back Emails  (billing.enabled required)
    |--------------------------------------------------------------------------
    | Sent to recently-churned subscribers to encourage re-subscription.
    */
    'win_back' => [
        1 => ['days' => 3,  'max_days' => 5],
        2 => ['days' => 14, 'max_days' => 17],
        3 => ['days' => 30, 'max_days' => 33],
    ],

    /*
    |--------------------------------------------------------------------------
    | Re-Engagement Emails
    |--------------------------------------------------------------------------
    | Sent to inactive users (no login / activity) after increasing intervals.
    */
    're_engagement' => [
        1 => ['days' => 7,  'max_days' => 9],
        2 => ['days' => 14, 'max_days' => 16],
        3 => ['days' => 21, 'max_days' => 23],
        4 => ['days' => 30, 'max_days' => 35],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trial Nudges  (billing.enabled required)
    |--------------------------------------------------------------------------
    | Windows are offsets in days relative to trial_ends_at.
    | Positive values = days before expiry; negative values = days after expiry.
    | Email fires when: trial_ends_at BETWEEN now()+window_start AND now()+window_end
    */
    'trial_nudge' => [
        1 => ['window_start' => 6,  'window_end' => 8],   // ~7 days before expiry
        2 => ['window_start' => 2,  'window_end' => 4],   // ~3 days before expiry
        3 => ['window_start' => -2, 'window_end' => 0],   // just expired
    ],

    /*
    |--------------------------------------------------------------------------
    | Trial-Ending Reminders  (billing.enabled required)
    |--------------------------------------------------------------------------
    | SendTrialEndingReminders targets users whose trial expires within this
    | many days from now. Uses UserSetting for single-per-day deduplication.
    */
    'trial_ending' => [
        'days_before_expiry' => 3,
    ],

];
