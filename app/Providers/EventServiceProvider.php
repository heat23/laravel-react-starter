<?php

namespace App\Providers;

use App\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
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
