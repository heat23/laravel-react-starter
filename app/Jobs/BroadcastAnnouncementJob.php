<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AdminAnnouncementNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastAnnouncementJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public readonly string $recipient,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $sentBy,
    ) {}

    public function handle(): void
    {
        $query = User::query()->whereNull('deleted_at');

        if ($this->recipient === 'admins') {
            $query->where('is_admin', true);
        }

        $notification = new AdminAnnouncementNotification(
            subject: $this->subject,
            body: $this->body,
            sentBy: $this->sentBy,
        );

        $query->chunk(200, function ($users) use ($notification) {
            foreach ($users as $user) {
                $user->notify($notification);
            }
        });
    }
}
