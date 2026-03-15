<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (method_exists($notifiable, 'hasVerifiedEmail') && $notifiable->hasVerifiedEmail()) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Welcome to {$appName}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Thanks for signing up for {$appName}. We're glad you're here.")
            ->line('Here are a few things to get you started:')
            ->line('1. **Set up your profile** — add your details and preferences')
            ->line('2. **Explore your dashboard** — see what you can do')
            ->line('3. **Check your settings** — customize your experience')
            ->action('Go to Your Dashboard', route('dashboard'))
            ->line('If you have any questions, just reply to this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'actionUrl' => route('dashboard'),
        ];
    }
}
