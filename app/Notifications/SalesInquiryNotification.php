<?php

namespace App\Notifications;

use App\Support\MailSanitizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalesInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $company,
        public readonly ?int $seatsNeeded,
        public readonly ?string $message,
    ) {}

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

        $safeNameHeader = MailSanitizer::sanitizeForHeader($this->name);
        $safeEmailHeader = MailSanitizer::sanitizeForHeader($this->email);

        $safeNameBody = MailSanitizer::sanitizeForMarkdown($this->name);
        $safeEmailBody = MailSanitizer::sanitizeForMarkdown($this->email);
        $safeCompanyBody = MailSanitizer::sanitizeForMarkdown($this->company);

        $mail = (new MailMessage)
            ->subject("[{$appName}] Enterprise Sales Inquiry from {$safeNameHeader}")
            ->replyTo($safeEmailHeader, $safeNameHeader)
            ->greeting("New Enterprise inquiry from {$safeNameHeader}")
            ->line("**Name:** {$safeNameBody}")
            ->line("**Email:** {$safeEmailBody}")
            ->line("**Company:** {$safeCompanyBody}")
            ->line('**Seats needed:** '.($this->seatsNeeded ?? 'Not specified'));

        if ($this->message) {
            $safeMessage = MailSanitizer::sanitizeForMarkdown($this->message);
            $mail->line('**Additional notes:**')->line($safeMessage);
        }

        return $mail->line('Reply directly to this email to respond to the prospect.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->company,
            'seats_needed' => $this->seatsNeeded,
        ];
    }
}
