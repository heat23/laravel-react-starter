<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReEngagementNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

    public function __construct(
        public readonly int $emailNumber,
        public readonly bool $isPaidUser = false,
        public readonly int $userScore = 0,
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
        // High-intent users (score ≥60) get upgrade-CTA variant for email 1 and 2
        if ($this->userScore >= 60 && in_array($this->emailNumber, [1, 2], true)) {
            $mail = $this->upgradeCtaVariant($notifiable);
        } else {
            $mail = match ($this->emailNumber) {
                1 => $this->gentleCheckIn($notifiable),
                2 => $this->feedbackRequest($notifiable),
                3 => $this->accountStatus($notifiable),
                4 => $this->valueTip($notifiable),
                default => $this->gentleCheckIn($notifiable),
            };
        }

        if ($line = $this->unsubscribeLine($notifiable)) {
            $mail->line($line);
        }

        return $mail;
    }

    private function upgradeCtaVariant(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $pricingUrl = route('pricing').'?utm_source=email&utm_campaign=reengagement_upgrade&utm_content=email_'.$this->emailNumber;

        return (new MailMessage)
            ->subject("You were close — here's what you were building")
            ->greeting("Hi {$notifiable->name}!")
            ->line("It's been a while since you visited {$appName}. You were building something real — your activity shows it.")
            ->line("You're using more of {$appName} than most free users. A Pro upgrade would remove the limits you've hit.")
            ->action('See what Pro unlocks', $pricingUrl)
            ->line('Questions? Reply to this email — we read every response.');
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

        $changelogItem = config('app.changelog_item') ?? 'recent improvements';

        $ctaUrl = $this->dashboardUrl('reengagement', 3);

        return (new MailMessage)
            ->subject('One thing changed since you last logged in')
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your data is safe — come back anytime. Here's what you've been missing:")
            ->line("Since your last visit, we shipped {$changelogItem}.")
            ->action('See What\'s New →', $ctaUrl)
            ->line('If you have questions or want to share feedback, just reply to this email. We read every response.');
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

    private function campaignVariant(): string
    {
        if ($this->userScore >= 60 && in_array($this->emailNumber, [1, 2], true)) {
            return 'upgrade_cta';
        }

        return match ($this->emailNumber) {
            1 => 'gentle_check_in',
            2 => 'feedback_request',
            3 => 'account_status',
            4 => 'value_tip',
            default => 'gentle_check_in',
        };
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
            'user_score' => $this->userScore,
            'campaign_variant' => $this->campaignVariant(),
        ];
    }
}
