<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Feedback $feedback) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $type = ucfirst($this->feedback->type);
        $userName = $this->feedback->user?->name ?? 'Guest';
        $userEmail = $this->feedback->user?->email ?? 'unknown';

        return (new MailMessage)
            ->subject("[{$appName}] New {$type} Feedback from {$userName}")
            ->greeting('New feedback received')
            ->line("**Type:** {$type}")
            ->line("**From:** {$userName} ({$userEmail})")
            ->line('**Message:**')
            ->line($this->feedback->message)
            ->action('View in Admin', config('app.url').'/admin/feedback/'.$this->feedback->id)
            ->line('Reply to the user or update the status from the admin panel.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'feedback_id' => $this->feedback->id,
            'type' => $this->feedback->type,
        ];
    }
}
