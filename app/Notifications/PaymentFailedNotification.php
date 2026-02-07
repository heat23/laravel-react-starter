<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $invoiceId,
        public readonly string $subscriptionId
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
            ->subject('Payment Failed - Action Required')
            ->line('We were unable to process your recent payment for your '.config('app.name').' subscription.')
            ->line('Please update your payment method to continue using your subscription without interruption.')
            ->action('Update Payment Method', route('billing.index'))
            ->line('If you have any questions, please contact our support team.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'invoiceId' => $this->invoiceId,
            'subscriptionId' => $this->subscriptionId,
            'actionUrl' => route('billing.index'),
        ];
    }
}
