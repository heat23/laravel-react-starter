<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

    public function __construct(
        public readonly int $daysRemaining,
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
        $daysLabel = $this->daysRemaining === 1 ? 'day' : 'days';
        $subject = $this->daysRemaining === 0
            ? "{$appName}: Your trial ends today"
            : "{$appName}: Your trial ends in {$this->daysRemaining} {$daysLabel}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your {$appName} trial ends in {$this->daysRemaining} {$daysLabel}. Subscribe now to keep full access.")
            ->action('Choose a Plan', config('features.billing.enabled') ? route('billing.index') : route('dashboard'))
            ->line('Questions? Reply to this email — we read every response.');

        if ($line = $this->unsubscribeLine($notifiable)) {
            $mail->line($line);
        }

        return $mail;
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trial_ending',
            'days_remaining' => $this->daysRemaining,
        ];
    }
}
