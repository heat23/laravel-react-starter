<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentActionRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $hostedInvoiceUrl,
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
            ->subject("{$appName}: Action required to renew your subscription")
            ->greeting("Hi {$firstName},")
            ->line('Your bank requires additional verification to complete your subscription renewal.')
            ->line('This is a standard security step (3D Secure / SCA) required by your card issuer. It only takes a moment to complete.')
            ->action('Complete Verification →', $this->hostedInvoiceUrl)
            ->line('If you do not complete this step, your subscription may be paused. The link above is valid for 24 hours.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_action_required',
            'invoiceId' => $this->invoiceId,
            'actionUrl' => $this->hostedInvoiceUrl,
        ];
    }
}
