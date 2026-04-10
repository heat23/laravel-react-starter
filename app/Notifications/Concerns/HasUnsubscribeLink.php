<?php

namespace App\Notifications\Concerns;

use Illuminate\Support\Facades\URL;

trait HasUnsubscribeLink
{
    /**
     * Returns a markdown unsubscribe line for the given notifiable, or null when the
     * notifiable has no id (e.g. anonymous / on-demand notifications).
     *
     * Callers must guard the ->line() call:
     *   if ($line = $this->unsubscribeLine($notifiable)) { $mail->line($line); }
     */
    public function unsubscribeLine(object $notifiable): ?string
    {
        if (empty($notifiable->id)) {
            return null;
        }

        $url = URL::temporarySignedRoute('unsubscribe', now()->addYear(), ['userId' => $notifiable->id]);

        return "To stop receiving these emails, [unsubscribe here]({$url}).";
    }
}
