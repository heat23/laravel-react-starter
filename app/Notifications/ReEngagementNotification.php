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
        public readonly int $emailNumber,
        public readonly bool $isPaidUser = false,
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
            4 => $this->valueTip($notifiable),
            default => $this->gentleCheckIn($notifiable),
        };
    }

    private function dashboardUrl(string $campaign, int $emailNumber): string
    {
        return route('dashboard').'?utm_source=email&utm_campaign='.$campaign.'&utm_content=email_'.$emailNumber;
    }

    private function gentleCheckIn(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $utmUrl = $this->dashboardUrl('reengagement', 1);

        if ($this->isPaidUser) {
            return (new MailMessage)
                ->subject("We haven't seen you in a while, {$notifiable->name}")
                ->greeting("Hi {$notifiable->name}!")
                ->line("It's been about a week since you last visited {$appName}. Your {$appName} subscription is still active — let's make the most of it.")
                ->line('Your account and all your data are exactly as you left them.')
                ->action('Return to Your Account', route('billing.index'))
                ->line('Have questions? Reply to this email — we read every response.');
        }

        return (new MailMessage)
            ->subject("We haven't seen you in a while")
            ->greeting("Hi {$notifiable->name}!")
            ->line("It's been about a week since you last visited {$appName}. Just checking in — is everything working the way you expected?")
            ->line('If you ran into any issues or have questions, reply to this email. We read every response.')
            ->action('Visit Your Account', $utmUrl)
            ->line('We\'re here to help if you need it.');
    }

    private function feedbackRequest(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $utmUrl = $this->dashboardUrl('reengagement', 2);

        if ($this->isPaidUser) {
            return (new MailMessage)
                ->subject("Your {$appName} account — can we help?")
                ->greeting("Hi {$notifiable->name}!")
                ->line("It's been two weeks since your last visit. As a subscriber, you have access to our priority support — just reply to this email.")
                ->line('**What would make {$appName} more useful for you?** One sentence is enough.')
                ->action('Go to Billing Portal', route('billing.portal'))
                ->line('Your account is active. Manage or pause your subscription anytime from the billing portal.');
        }

        return (new MailMessage)
            ->subject('Quick question about your experience')
            ->greeting("Hi {$notifiable->name}!")
            ->line("It's been two weeks since your last visit to {$appName}. We'd love to understand why — your feedback helps us improve.")
            ->line('**What would bring you back?** Reply with one thing we could do better. Even a one-line response helps.')
            ->action('Come Back and Explore', $utmUrl)
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

    private function valueTip(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $utmUrl = $this->dashboardUrl('reengagement', 4);

        return (new MailMessage)
            ->subject("One thing you might have missed in {$appName}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("It's been a few weeks — here's a feature worth knowing about:")
            ->line('**API tokens** let you connect {$appName} to your existing workflow. One command and your tools are in sync.')
            ->line('Takes about 30 seconds to set up, and it changes how you use the product.')
            ->action('Set Up an API Token', route('settings.tokens'))
            ->line('Questions? Just reply to this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "re_engagement_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
            'is_paid' => $this->isPaidUser,
        ];
    }
}
