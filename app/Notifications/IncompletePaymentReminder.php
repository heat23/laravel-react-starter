<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncompletePaymentReminder extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

    public function __construct(
        public readonly string $confirmUrl,
        public readonly int $hoursRemaining,
        public readonly bool $urgent = false,
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
        $appName = config('app.name');
        $fullName = $notifiable->name ?? '';
        $firstName = explode(' ', $fullName)[0] ?: $fullName ?: 'there';

        $subject = $this->urgent
            ? "{$appName}: Your subscription expires in ~1 hour"
            : "{$appName}: Complete your payment to keep your subscription active";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hi {$firstName},")
            ->line('Your subscription is waiting for payment confirmation.')
            ->line("You have **{$this->hoursRemaining} ".($this->hoursRemaining === 1 ? 'hour' : 'hours').' remaining** before it expires.')
            ->action('Complete Payment Now', $this->confirmUrl)
            ->line('If you have any questions, please contact our support team.');

        if ($line = $this->unsubscribeLine($notifiable)) {
            $mail->line($line);
        }

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'incomplete_payment_reminder',
            'confirm_url' => $this->confirmUrl,
            'hours_remaining' => $this->hoursRemaining,
            'urgent' => $this->urgent,
        ];
    }
}
