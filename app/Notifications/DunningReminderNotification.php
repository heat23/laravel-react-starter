<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DunningReminderNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

    public function __construct(
        public readonly int $emailNumber,
        public readonly string $planName = 'your plan'
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
        $mail = match ($this->emailNumber) {
            1 => $this->gentleReminderEmail($notifiable),
            2 => $this->urgencyEmail($notifiable),
            3 => $this->finalNoticeEmail($notifiable),
            default => $this->gentleReminderEmail($notifiable),
        };

        if ($line = $this->unsubscribeLine($notifiable)) {
            $mail->line($line);
        }

        return $mail;
    }

    private function gentleReminderEmail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your payment method needs updating')
            ->greeting("Hi {$notifiable->name},")
            ->line('We tried to charge your card a few days ago and it didn\'t go through.')
            ->line('Your account is still active — update your card to keep it that way.')
            ->action('Update Payment Method', route('billing.index'))
            ->line('If you\'ve already updated your payment method, you can ignore this email.');
    }

    private function urgencyEmail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action needed — your subscription will be paused')
            ->greeting("Hi {$notifiable->name},")
            ->line('It\'s been a week since your payment failed.')
            ->line("If we can't process payment soon, your **{$this->planName}** subscription will be paused and you'll lose access to premium features.")
            ->action('Update Payment Method', route('billing.index'))
            ->line('Having trouble? Reply to this email — we can help.');
    }

    private function finalNoticeEmail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Final notice — subscription will cancel tomorrow')
            ->greeting("Hi {$notifiable->name},")
            ->line("This is our last reminder before your **{$this->planName}** subscription is cancelled.")
            ->line('After cancellation, you\'ll lose access to all premium features included in your plan.')
            ->action('Save My Subscription', route('billing.index'))
            ->line('We\'d hate to see you go. If there\'s an issue with billing, just reply to this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "dunning_reminder_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
            'plan_name' => $this->planName,
            'actionUrl' => route('billing.index'),
        ];
    }
}
