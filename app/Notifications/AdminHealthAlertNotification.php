<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminHealthAlertNotification extends Notification
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
        return ['database'];
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
