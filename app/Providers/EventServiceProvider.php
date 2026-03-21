<?php

namespace App\Providers;

use App\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Welcome email (sequence email 1) is sent by the emails:send-welcome-sequence
        // scheduled command (daily at 09:00). Sending it here at registration would fire
        // before email verification, storing a database-channel record that blocks the
        // command from ever delivering the mail channel copy to newly-verified users.
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Override to prevent the framework from also registering its
     * synchronous SendEmailVerificationNotification listener.
     */
    protected function configureEmailVerification(): void
    {
        // Handled by our queued listener above.
    }
}
