<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpgradeNudgeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $score,
    ) {}

    /** @return array<int, string> */
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

        $copyLine = $this->score >= 80
            ? "You're one of our most engaged users — and you'd get a lot more from a paid plan."
            : "Based on how you're using {$appName}, you're ready for more.";

        return (new MailMessage)
            ->subject("{$appName}: Ready to upgrade?")
            ->greeting("Hi {$notifiable->name}!")
            ->line($copyLine)
            ->line('Paid plans unlock higher limits, advanced features, and priority support.')
            ->action('See Plans', route('pricing'))
            ->line('Questions? Reply to this email.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'upgrade_nudge',
            'score' => $this->score,
        ];
    }
}
