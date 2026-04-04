<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LimitThresholdNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

    public function __construct(
        public readonly string $limitKey,
        public readonly int $threshold,
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
        $limitLabel = str_replace('_', ' ', $this->limitKey);

        if ($this->threshold >= 100) {
            $mail = (new MailMessage)
                ->subject("{$appName}: You've reached your {$limitLabel} limit")
                ->greeting("Hi {$notifiable->name}!")
                ->line("You've used 100% of your {$limitLabel} limit on the current plan.")
                ->line('Upgrade to continue creating without interruption.')
                ->action('Upgrade Your Plan', route('pricing'))
                ->line('Questions? Reply to this email.');
        } else {
            $mail = (new MailMessage)
                ->subject("{$appName}: You're at {$this->threshold}% of your {$limitLabel} limit")
                ->greeting("Hi {$notifiable->name}!")
                ->line("You've used {$this->threshold}% of your {$limitLabel} limit. Consider upgrading before you hit the cap.")
                ->action('See Upgrade Options', route('pricing'))
                ->line('Questions? Reply to this email.');
        }

        if ($line = $this->unsubscribeLine($notifiable)) {
            $mail->line($line);
        }

        return $mail;
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'limit_threshold',
            'limit_key' => $this->limitKey,
            'threshold' => $this->threshold,
        ];
    }
}
