<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialNudgeNotification extends Notification implements ShouldQueue
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
            1 => $this->halfwayEmail($notifiable),
            2 => $this->urgencyEmail($notifiable),
            3 => $this->expiredEmail($notifiable),
            default => $this->halfwayEmail($notifiable),
        };
    }

    private function halfwayEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('Your trial is halfway through')
            ->greeting("Hi {$notifiable->name}!")
            ->line("You're 7 days into your {$appName} trial. Here's a quick check-in:")
            ->line('**Have you explored the features that matter most to you?** If not, now is a great time to dive in.')
            ->line('Your trial gives you full access to everything — no features are held back.')
            ->action('Explore Your Account', route('dashboard'))
            ->line('Questions about which plan is right for you? Reply to this email.');
    }

    private function urgencyEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('3 days left on your trial')
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your {$appName} trial ends in 3 days. To keep your access uninterrupted, choose a plan that fits your needs.")
            ->line('**What happens when your trial ends?** You\'ll keep your account and data, but premium features will be restricted until you subscribe.')
            ->action('Choose a Plan', route('dashboard'))
            ->line('Need more time? Reply to this email and we\'ll see what we can do.');
    }

    private function expiredEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('Your trial has ended')
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your {$appName} trial period has ended. Your account and data are still here — nothing has been deleted.")
            ->line('To regain access to premium features, subscribe to a plan.')
            ->action('Subscribe Now', route('dashboard'))
            ->line('If you decided this isn\'t for you, no hard feelings. Your data will be retained for 30 days.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "trial_nudge_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
        ];
    }
}
