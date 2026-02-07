<?php

namespace App\Console\Commands;

use App\Notifications\IncompletePaymentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class CheckIncompletePayments extends Command
{
    protected $signature = 'subscriptions:check-incomplete';

    protected $description = 'Check for incomplete subscription payments and send reminder notifications';

    public function handle(): int
    {
        $incompleteSubscriptions = Subscription::where('stripe_status', 'incomplete')
            ->where('created_at', '>', now()->subHours(23))
            ->where('created_at', '<', now()->subMinutes(30))
            ->with('user')
            ->get();

        if ($incompleteSubscriptions->isEmpty()) {
            $this->info('No incomplete payments found.');

            return self::SUCCESS;
        }

        $remindersSent = 0;

        foreach ($incompleteSubscriptions as $subscription) {
            try {
                $hoursElapsed = (int) $subscription->created_at->diffInHours(now());

                // Send reminders at 1 hour and 12 hours (using ranges to avoid missing reminders)
                $sendOneHour = $hoursElapsed >= 1 && $hoursElapsed < 2;
                $sendTwelveHour = $hoursElapsed >= 12 && $hoursElapsed < 13;

                if (! $sendOneHour && ! $sendTwelveHour) {
                    continue;
                }

                // Deduplicate: check if we already sent this reminder recently
                $alreadySent = $subscription->user->notifications()
                    ->where('type', IncompletePaymentReminder::class)
                    ->where('created_at', '>', now()->subMinutes(30))
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $hoursRemaining = 23 - $hoursElapsed;
                $confirmUrl = route('billing.index');

                $subscription->user->notify(new IncompletePaymentReminder(
                    confirmUrl: $confirmUrl,
                    hoursRemaining: $hoursRemaining
                ));

                $remindersSent++;

                Log::info('Incomplete payment reminder sent', [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                    'hours_elapsed' => $hoursElapsed,
                    'hours_remaining' => $hoursRemaining,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send incomplete payment reminder', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$remindersSent} incomplete payment reminders.");

        return self::SUCCESS;
    }
}
