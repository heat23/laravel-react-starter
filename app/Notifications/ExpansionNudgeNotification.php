<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpansionNudgeNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

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

        $mail = (new MailMessage)
            ->subject("{$appName}: Your team is growing — consider upgrading")
            ->greeting("Hi {$notifiable->name}!")
            ->line("You've been getting great value from {$appName}. As your team grows, the Team plan offers more seats and advanced collaboration features.")
            ->action('See Team Plans', route('pricing'))
            ->line('Questions about upgrading? Reply to this email.');

        if ($line = $this->unsubscribeLine($notifiable)) {
            $mail->line($line);
        }

        return $mail;
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'expansion_nudge',
        ];
    }
}
