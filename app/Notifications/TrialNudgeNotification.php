<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasUnsubscribeLink;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialNudgeNotification extends Notification implements ShouldQueue
{
    use HasUnsubscribeLink, Queueable;

    public function __construct(
        public readonly int $emailNumber,
        public readonly ?Carbon $trialEndsAt = null,
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
            1 => $this->halfwayEmail($notifiable),
            2 => $this->urgencyEmail($notifiable),
            3 => $this->expiredEmail($notifiable),
            default => $this->halfwayEmail($notifiable),
        };

        if ($line = $this->unsubscribeLine($notifiable)) {
            $mail->line($line);
        }

        return $mail;
    }

    private function daysLeft(): int
    {
        if (! $this->trialEndsAt) {
            return 7;
        }

        return max(0, (int) now()->diffInDays($this->trialEndsAt, false));
    }

    private function halfwayEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $daysLeft = $this->daysLeft();

        $dayLabel = $daysLeft === 1 ? 'day' : 'days';

        return (new MailMessage)
            ->subject("{$appName}: {$daysLeft} {$dayLabel} left in your trial")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your {$appName} trial ends in {$daysLeft} {$dayLabel}. Here's a quick check-in:")
            ->line('**Have you explored the features that matter most to you?** If not, now is a great time to dive in.')
            ->line('Your trial gives you full access to everything — no features are held back.')
            ->action('See Plans & Pricing', config('features.billing.enabled') ? route('pricing') : route('dashboard'))
            ->line('Questions about which plan is right for you? Reply to this email.');
    }

    private function urgencyEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $daysLeft = $this->daysLeft();
        $subject = match (true) {
            $daysLeft === 0 => "{$appName}: Your trial ends today",
            $daysLeft === 1 => "{$appName}: Your trial ends tomorrow",
            default => "{$appName}: Your trial ends in {$daysLeft} days",
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your {$appName} trial ends in {$daysLeft} ".($daysLeft === 1 ? 'day' : 'days').'. To keep your access uninterrupted, choose a plan that fits your needs.')
            ->line('**What happens when your trial ends?** You\'ll keep your account and data, but premium features will be restricted until you subscribe.')
            ->action('Choose a Plan', config('features.billing.enabled') ? route('billing.index') : route('dashboard'))
            ->line('Need more time? Reply to this email and we\'ll see what we can do.');
    }

    private function expiredEmail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("{$appName}: Your trial has ended")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your {$appName} trial period has ended. Your account and data are still here.")
            ->line('To regain access to premium features, subscribe to a plan.')
            ->action('Subscribe Now', config('features.billing.enabled') ? route('billing.index') : route('dashboard'))
            ->line('If you decided this isn\'t for you, no hard feelings — your account stays active.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => "trial_nudge_{$this->emailNumber}",
            'email_number' => $this->emailNumber,
            'trial_ends_at' => $this->trialEndsAt?->toISOString(),
        ];
    }
}
