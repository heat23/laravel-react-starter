<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WinBackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Email variant number: 1 (day 3), 2 (day 14), 3 (day 30).
     */
    public function __construct(
        public readonly int $emailNumber,
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
        $fullName = $notifiable->name ?? '';
        $firstName = explode(' ', $fullName)[0] ?: $fullName ?: 'there';
        $appName = config('app.name');

        return match ($this->emailNumber) {
            1 => $this->emailOne($firstName, $appName),
            2 => $this->emailTwo($firstName, $appName),
            default => $this->emailThree($firstName, $appName),
        };
    }

    private function emailOne(string $firstName, string $appName): MailMessage
    {
        return (new MailMessage)
            ->subject("We miss you, {$firstName} — anything we could have done better?")
            ->greeting("Hi {$firstName},")
            ->line("You recently canceled your {$appName} subscription, and we wanted to reach out.")
            ->line('Was it something we could have done better? Your feedback genuinely shapes what we build next.')
            ->action('Share Quick Feedback →', route('contact.show'))
            ->line('Or if you changed your mind, your account is ready and waiting:')
            ->action('Reactivate Subscription →', route('pricing'));
    }

    private function emailTwo(string $firstName, string $appName): MailMessage
    {
        return (new MailMessage)
            ->subject("{$appName}: Here's what's new since you left")
            ->greeting("Hi {$firstName},")
            ->line("A lot has improved in {$appName} since you left. We've been shipping hard.")
            ->line("We'd love to have you back to try the latest features.")
            ->action('See What\'s New →', route('changelog'))
            ->action('Reactivate Subscription →', route('pricing'));
    }

    private function emailThree(string $firstName, string $appName): MailMessage
    {
        return (new MailMessage)
            ->subject("Last chance: come back to {$appName}")
            ->greeting("Hi {$firstName},")
            ->line('This is our last check-in. We hope things are going well.')
            ->line("If you ever want to come back, your {$appName} account is still here with all your data intact.")
            ->action('Come Back →', route('pricing'))
            ->line('After this, we\'ll stop reaching out. We only want to hear from you if it\'s useful to you.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'win_back',
            'email_number' => $this->emailNumber,
            'actionUrl' => route('pricing'),
        ];
    }
}
