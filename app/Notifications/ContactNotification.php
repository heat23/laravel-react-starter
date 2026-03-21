<?php

namespace App\Notifications;

use App\Models\ContactSubmission;
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

        return (new MailMessage)
            ->subject("[{$appName}] New Contact: {$this->submission->subject}")
            ->replyTo($this->submission->email, $this->submission->name)
            ->greeting("New contact from {$this->submission->name}")
            ->line("**Subject:** {$this->submission->subject}")
            ->line("**From:** {$this->submission->name} <{$this->submission->email}>")
            ->line('**Message:**')
            ->line($this->submission->message)
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
