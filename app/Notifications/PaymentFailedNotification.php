<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

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
        $fullName = $notifiable->name ?? '';
        $firstName = explode(' ', $fullName)[0] ?: $fullName ?: 'there';
        $appName = config('app.name');

        $mail = (new MailMessage)
            ->subject("{$appName}: Your payment needs attention")
            ->greeting("Hi {$firstName},")
            ->line("Don't worry — this happens sometimes and is easy to fix.")
            ->line("We weren't able to process your last payment for your {$appName} subscription. Your account remains active for now, but we'll pause your subscription in 3 days if we can't collect payment.")
            ->line('To keep your access uninterrupted, update your payment method using the button below.')
            ->action('Update Payment Method →', route('billing.index'))
            ->line("If you think this is a mistake or your card was recently updated, reply to this email and we'll sort it out.");

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
            'type' => 'payment_failed',
            'invoiceId' => $this->invoiceId,
            'subscriptionId' => $this->subscriptionId,
            'actionUrl' => route('billing.index'),
        ];
    }
}
