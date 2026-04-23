<?php

/**
 * Email Sequence Schedules
 *
 * Centralised timing configuration for lifecycle email sequences.
 *
 * "days" / "max_days" fields are relative to the user's created_at.
 * A user created N days ago receives email X when:
 *   created_at <= now() - days   AND   created_at > now() - max_days
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Welcome Sequence
    |--------------------------------------------------------------------------
    | Email 1 is sent by the Registered event listener; this config governs
    | the follow-up emails dispatched by the emails:send-welcome-sequence command.
    */
    'welcome_sequence' => [
        2 => ['days' => 1, 'max_days' => 2],
        3 => ['days' => 3, 'max_days' => 5],
    ],

];
