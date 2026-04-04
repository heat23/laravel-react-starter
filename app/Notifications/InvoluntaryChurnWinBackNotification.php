<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoluntaryChurnWinBackNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

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
            ->subject("{$appName}: We couldn't save your subscription — come back anytime")
            ->greeting("Hi {$firstName},")
            ->line("We're sorry your {$appName} subscription ended due to a payment issue.")
            ->line('It happens to everyone — cards expire, banks block charges. We hope it was just a technical glitch and not that we let you down.')
            ->line('Your account and all your data are still here. You can reactivate your subscription at any time with a fresh payment method — no re-setup needed.')
            ->action('Reactivate Subscription →', route('pricing'))
            ->line('If you have questions or ran into a problem we could fix, just reply to this email.');

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
            'type' => 'involuntary_churn_win_back',
            'actionUrl' => route('pricing'),
        ];
    }
}
