<?php

namespace App\Notifications;

use App\Models\Feedback;
use App\Support\MailSanitizer;
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

        // Header-safe variants (strip CRLF + character allowlist)
        $safeTypHeader = MailSanitizer::sanitizeForHeader($type);
        $safeUserNameHeader = MailSanitizer::sanitizeForHeader($userName);

        // Body-safe variants (strip HTML + escape markdown)
        $safeType = MailSanitizer::sanitizeForMarkdown($type);
        $safeUserName = MailSanitizer::sanitizeForMarkdown($userName);
        $safeUserEmail = MailSanitizer::sanitizeForMarkdown($userEmail);

        if (trim($safeUserName) === '') {
            $safeUserName = 'Unknown';
        }

        if (trim($safeUserNameHeader) === '') {
            $safeUserNameHeader = 'Unknown';
        }

        return (new MailMessage)
            ->subject("[{$appName}] New {$safeTypHeader} Feedback from {$safeUserNameHeader}")
            ->greeting('New feedback received')
            ->line("**Type:** {$safeType}")
            ->line("**From:** {$safeUserName} ({$safeUserEmail})")
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
