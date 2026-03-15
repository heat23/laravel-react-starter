<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReEngagementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $emailNumber
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
        return match ($this->emailNumber) {
            1 => $this->gentleCheckIn($notifiable),
            2 => $this->feedbackRequest($notifiable),
            3 => $this->accountStatus($notifiable),
            default => $this->gentleCheckIn($notifiable),
        };
    }

    private function gentleCheckIn(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("We haven't seen you in a while")
            ->greeting("Hi {$notifiable->name}!")
            ->line("It's been about a week since you last visited {$appName}. Just checking in — is everything working the way you expected?")
            ->line('If you ran into any issues or have questions, reply to this email. We read every response.')
            ->action('Visit Your Account', route('dashboard'))
            ->line('We\'re here to help if you need it.');
    }

    private function feedbackRequest(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('Quick question about your experience')
            ->greeting("Hi {$notifiable->name}!")
            ->line("It's been two weeks since your last visit to {$appName}. We'd love to understand why — your feedback helps us improve.")
            ->line('**What would bring you back?** Reply with one thing we could do better. Even a one-line response helps.')
            ->action('Come Back and Explore', route('dashboard'))
            ->line('Your account is still active and your data is safe.');
    }

    private function accountStatus(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Your {$appName} account is still active")
            ->greeting("Hi {$notifiable->name}!")
            ->line("It's been 30 days since your last visit. Your account and all your data are still here — nothing has been deleted.")
            ->line('If you\'d like to continue using your account, just log in whenever you\'re ready.')
            ->action('Log In', route('login'))
            ->line('If you no longer need your account, you can manage your data from your profile settings.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "re_engagement_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
        ];
    }
}
