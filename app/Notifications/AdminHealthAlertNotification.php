<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminHealthAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $alerts
     */
    public function __construct(
        private array $alerts,
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
        $alertCount = count($this->alerts);
        $alertLabel = $alertCount === 1 ? 'alert' : 'alerts';

        $mail = (new MailMessage)
            ->subject("[{$appName}] Health check alert — {$alertCount} {$alertLabel} detected")
            ->greeting('Health Check Alert')
            ->line("The following {$alertLabel} were detected at ".now()->toDateTimeString().':');

        foreach ($this->alerts as $key => $detail) {
            $mail->line('— '.($detail['message'] ?? $key));
        }

        if (config('features.admin.enabled', false)) {
            $mail->action('View Health Dashboard →', route('admin.health'));
        }

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'health_alert',
            'alerts' => $this->alerts,
            'message' => $this->buildMessage(),
        ];
    }

    private function buildMessage(): string
    {
        $parts = [];

        foreach ($this->alerts as $key => $detail) {
            $parts[] = match ($key) {
                'failed_jobs' => "Failed jobs: {$detail['count']} (threshold: {$detail['threshold']})",
                'health_status' => "Health: {$detail['status']}",
                'webhook_failure_rate' => "Webhook failure rate: {$detail['rate']}% (threshold: {$detail['threshold']}%)",
                default => "{$key}: {$detail['message']}",
            };
        }

        return 'Health alerts: '.implode('; ', $parts);
    }
}
