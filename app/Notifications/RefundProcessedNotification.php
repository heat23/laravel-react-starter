<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundProcessedNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

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
        $appName = config('app.name');
        $currencySymbol = match (strtolower($this->currency)) {
            'eur' => '€',
            'gbp' => '£',
            'cad', 'aud', 'nzd' => $this->currency.'$',
            default => '$',
        };
        $formattedAmount = $currencySymbol.number_format($this->amountRefunded / 100, 2);
        $fullName = $notifiable->name ?? '';
        $firstName = explode(' ', $fullName)[0] ?: $fullName ?: 'there';

        $mail = (new MailMessage)
            ->subject("{$appName}: Your {$formattedAmount} refund is confirmed")
            ->greeting("Hi {$firstName},")
            ->line("We've processed a refund of {$formattedAmount} to your payment method.");

        if ($this->reason) {
            $mail->line('Reason: '.$this->reason);
        }

        $mail->line('The refund should appear in your account within 5-10 business days.')
            ->action('View Billing', route('billing.index'))
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
            'type' => 'refund_processed',
            'charge_id' => $this->chargeId,
            'amount_refunded' => $this->amountRefunded,
            'currency' => $this->currency,
            'reason' => $this->reason,
        ];
    }
}
