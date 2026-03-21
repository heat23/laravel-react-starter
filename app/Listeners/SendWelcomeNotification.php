<?php

namespace App\Listeners;

use App\Notifications\WelcomeSequenceNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeNotification implements ShouldQueue
{
    public function handle(Registered $event): void
    {
        $event->user->notify(new WelcomeSequenceNotification(1));
    }
}
