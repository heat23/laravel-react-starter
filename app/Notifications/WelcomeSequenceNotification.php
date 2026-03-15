<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSequenceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $emailNumber
    ) {}

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
        return match ($this->emailNumber) {
            1 => $this->welcomeEmail($notifiable),
            2 => $this->gettingStartedEmail($notifiable),
            3 => $this->advancedFeaturesEmail($notifiable),
            default => $this->welcomeEmail($notifiable),
        };
    }

    private function welcomeEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Welcome to {$appName}!")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Thanks for signing up for {$appName}. You've made a great choice.")
            ->line('Your account is ready to go. Here\'s what you can do right now:')
            ->line('**Explore your dashboard** — get an overview of your account')
            ->action('Go to Dashboard', route('dashboard'))
            ->line("We'll send you a couple more tips over the next few days to help you get the most out of {$appName}.");
    }

    private function gettingStartedEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("3 things to try first in {$appName}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Now that you've had a day to look around, here are the three things our most successful users do first:")
            ->line('**1. Configure your settings** — set your timezone and theme preferences')
            ->line('**2. Set up your profile** — make your account feel like yours')
            ->line('**3. Explore the features** — see what\'s available to you')
            ->action('Open Settings', route('dashboard'))
            ->line('Each one takes less than a minute.');
    }

    private function advancedFeaturesEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Unlock the full power of {$appName}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("You've been with us for a few days now. Here are some features you might not have discovered yet:")
            ->line('**API tokens** — integrate with your existing tools')
            ->line('**Webhooks** — get notified when things happen')
            ->line('**Team features** — collaborate with your colleagues')
            ->action('Explore Features', route('dashboard'))
            ->line('Have questions? Reply to this email — we read every response.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "welcome_sequence_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
        ];
    }
}
