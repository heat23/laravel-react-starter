<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncompletePaymentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $confirmUrl,
        public readonly int $hoursRemaining
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
        return (new MailMessage)
            ->subject("Complete Your Subscription - {$this->hoursRemaining} Hours Remaining")
            ->line('Your subscription is waiting for payment confirmation.')
            ->line("You have **{$this->hoursRemaining} hours remaining** before it expires.")
            ->action('Complete Payment Now', $this->confirmUrl)
            ->line('If you have any questions, please contact our support team.');
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
        ];
    }
}
