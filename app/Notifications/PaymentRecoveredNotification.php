<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRecoveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $invoiceId,
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
        $fullName = $notifiable->name ?? '';
        $firstName = explode(' ', $fullName)[0] ?: $fullName ?: 'there';
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("{$appName}: Payment successful — your subscription is active")
            ->greeting("Hi {$firstName},")
            ->line('Great news — your payment was successfully processed and your subscription is fully active.')
            ->line('You now have uninterrupted access to all your plan features. No further action is needed.')
            ->action('Go to Dashboard →', route('dashboard'))
            ->line('Thank you for staying with us!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_recovered',
            'invoiceId' => $this->invoiceId,
            'actionUrl' => route('dashboard'),
        ];
    }
}
