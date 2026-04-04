<?php

namespace App\Notifications;

use App\Models\ContactSubmission;
use App\Support\MailSanitizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ContactSubmission $submission) {}

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

        // Header-safe values (CRLF/null-byte stripped) — used in SMTP headers only.
        $safeSubjectHeader = MailSanitizer::sanitizeForHeader($this->submission->subject);
        $safeEmailHeader = MailSanitizer::sanitizeForHeader($this->submission->email);
        $safeNameHeader = MailSanitizer::sanitizeForHeader($this->submission->name);

        // Markdown-safe values — used in body lines.
        $safeSubjectBody = MailSanitizer::sanitizeForMarkdown($this->submission->subject);
        $safeNameBody = MailSanitizer::sanitizeForMarkdown($this->submission->name);
        $safeEmailBody = MailSanitizer::sanitizeForMarkdown($this->submission->email);
        $safeMessage = MailSanitizer::sanitizeForMarkdown($this->submission->message);

        return (new MailMessage)
            ->subject("[{$appName}] New Contact: {$safeSubjectHeader}")
            ->replyTo($safeEmailHeader, $safeNameHeader)
            ->greeting("New contact from {$safeNameHeader}")
            ->line("**Subject:** {$safeSubjectBody}")
            ->line("**From:** {$safeNameBody} <{$safeEmailBody}>")
            ->line('**Message:**')
            ->line($safeMessage)
            ->line('Reply directly to this email to respond to the sender.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'subject' => $this->submission->subject,
        ];
    }
}
