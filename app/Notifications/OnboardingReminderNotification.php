<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnboardingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $emailNumber,
        public readonly ?string $ctaUrl = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->emailNumber) {
            1 => $this->gettingStartedEmail($notifiable),
            2 => $this->featureHighlightEmail($notifiable),
            3 => $this->needHelpEmail($notifiable),
            default => $this->gettingStartedEmail($notifiable),
        };
    }

    private function gettingStartedEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $url = $this->ctaUrl ?? route('dashboard');

        return (new MailMessage)
            ->subject('3 things to set up in your first 5 minutes')
            ->greeting("Hi {$notifiable->name}!")
            ->line("You signed up for {$appName} recently — here's how to get the most out of it.")
            ->line('**1. Complete your profile** — add your name and preferences')
            ->line('**2. Configure your settings** — set your timezone and theme')
            ->line('**3. Explore the dashboard** — see everything at a glance')
            ->action('Complete Your Setup', $url)
            ->line('Takes about 5 minutes. You\'ll be glad you did.');
    }

    private function featureHighlightEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Did you know {$appName} can do this?")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Most new users miss this: {$appName} can help you work faster with your dashboard.")
            ->line('Your dashboard gives you a real-time overview of everything that matters — no digging through menus.')
            ->action('Try It Now', route('dashboard'))
            ->line('Customize it to show exactly what you need.');
    }

    private function needHelpEmail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Quick question — is everything working?')
            ->greeting("Hi {$notifiable->name}!")
            ->line('It\'s been a week since you joined. Just checking in — is everything working the way you expected?')
            ->line('If you\'re stuck on anything, reply to this email. We read every response.')
            ->action('Visit Your Dashboard', route('dashboard'))
            ->line('We\'re here to help if you need it.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "onboarding_reminder_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
        ];
    }
}
