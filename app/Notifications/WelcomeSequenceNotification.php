<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSequenceNotification extends Notification implements ShouldQueue
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
            1 => $this->welcomeEmail($notifiable),
            2 => $this->gettingStartedEmail($notifiable),
            3 => $this->advancedFeaturesEmail($notifiable),
            default => $this->welcomeEmail($notifiable),
        };
    }

    private function welcomeEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        $mail = (new MailMessage)
            ->subject("Welcome to {$appName} — Let's Get You Started")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Thanks for signing up for {$appName}. Your account is ready. Here's how to get set up in the next 10 minutes:")
            ->line('**1. Set up your profile (2 min)** — add your details and customize your experience');

        if (config('features.billing.enabled', false)) {
            $mail->line('**2. Connect your Stripe account (5 min)** — configure your subscription and billing details');
        }

        if (config('features.api_tokens.enabled', true)) {
            $mail->line('**3. Generate your first API token (1 min)** — integrate '.$appName.' with your existing tools');
        }

        $mail->action('Go to Your Dashboard', route('dashboard'))
            ->line("We'll send you a couple more tips over the next few days to help you get the most out of {$appName}.");

        return $mail;
    }

    private function gettingStartedEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("3 things to try first in {$appName}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Now that you've had a day to look around, here are the three things our most successful users do first:")
            ->line('**1. Configure your settings** — set your timezone and theme preferences')
            ->line('**2. Set up your profile** — make your account feel like yours')
            ->line('**3. Explore the features** — see what\'s available to you')
            ->action('Open Settings', route('profile.edit'))
            ->line('Each one takes less than a minute.');
    }

    private function advancedFeaturesEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        // Deep-link to the most relevant enabled feature
        $ctaUrl = match (true) {
            config('features.api_tokens.enabled', true) => route('settings.tokens'),
            config('features.webhooks.enabled', false) => route('settings.webhooks'),
            default => route('dashboard'),
        };

        $mail = (new MailMessage)
            ->subject("Unlock the full power of {$appName}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("You've been with us for a few days now. Here are some features you might not have discovered yet:");

        // Only mention features that are enabled in this deployment
        if (config('features.api_tokens.enabled', true)) {
            $mail->line('**API tokens** — integrate with your existing tools');
        }

        if (config('features.webhooks.enabled', false)) {
            $mail->line('**Webhooks** — get notified when things happen in real time');
        }

        if (config('features.two_factor.enabled', false)) {
            $mail->line('**Two-factor authentication** — add an extra layer of security to your account');
        }

        if (config('features.billing.enabled', false)) {
            $mail->line('**Billing & plans** — manage your subscription and upgrade to unlock more');
        }

        $mail->action('Explore Advanced Features', $ctaUrl)
            ->line('Have questions? Reply to this email — we read every response.');

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "welcome_sequence_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
        ];
    }
}
