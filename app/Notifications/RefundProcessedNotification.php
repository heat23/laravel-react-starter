<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $chargeId,
        public readonly int $amountRefunded,
        public readonly string $currency = 'usd',
        public readonly ?string $reason = null
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
        $formattedAmount = '$'.number_format($this->amountRefunded / 100, 2);

        $mail = (new MailMessage)
            ->subject('Refund Processed - '.config('app.name'))
            ->line("We've processed a refund of {$formattedAmount} to your payment method.");

        if ($this->reason) {
            $mail->line('Reason: '.$this->reason);
        }

        return $mail
            ->line('The refund should appear in your account within 5-10 business days.')
            ->action('View Billing', route('billing.index'))
            ->line('If you have any questions, please contact our support team.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'refund_processed',
            'charge_id' => $this->chargeId,
            'amount_refunded' => $this->amountRefunded,
            'currency' => $this->currency,
            'reason' => $this->reason,
        ];
    }
}
