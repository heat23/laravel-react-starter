<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpansionNudgeNotification extends Notification implements ShouldQueue
{
    use Queueable;

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

        return (new MailMessage)
            ->subject("{$appName}: Your team is growing — consider upgrading")
            ->greeting("Hi {$notifiable->name}!")
            ->line("You've been getting great value from {$appName}. As your team grows, the Team plan offers more seats and advanced collaboration features.")
            ->action('See Team Plans', route('pricing'))
            ->line('Questions about upgrading? Reply to this email.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'expansion_nudge',
        ];
    }
}
