<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $user->loadCount(['settings', 'tokens']);
        // Eager load subscriptions to avoid N+1 from subscribed(), subscription(), onTrial()
        $user->load('subscriptions');

        $stats = [
            'days_since_signup' => (int) $user->created_at->diffInDays(now()),
            'email_verified' => $user->hasVerifiedEmail(),
            'has_subscription' => method_exists($user, 'subscribed') && $user->subscribed('default'),
            'plan_name' => $this->getPlanName($user),
            'settings_count' => $user->settings_count,
            'tokens_count' => $user->tokens_count,
            'login_streak' => $this->getLoginStreak($user),
        ];

        $recentActivity = AuditLog::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'event', 'created_at'])
            ->map(fn (AuditLog $log) => [
                'event' => $log->event,
                'created_at' => $log->created_at?->toISOString(),
            ])
            ->toArray();

        return Inertia::render('App/Dashboard', [
            'stats' => $stats,
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * Count consecutive days the user has logged in (via audit_logs), up to 30 days back.
     */
    private function getLoginStreak(User $user): int
    {
        if (! class_exists(AuditLog::class)) {
            return 0;
        }

        // Get distinct login dates for the past 30 days
        $loginDates = AuditLog::where('user_id', $user->id)
            ->where('event', 'auth.login')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(fn ($dt) => $dt->toDateString())
            ->unique()
            ->values();

        if ($loginDates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $checkDate = now()->toDateString();

        // Allow today or yesterday as the streak start (user may not have logged in today yet)
        if ($loginDates->first() !== $checkDate) {
            $checkDate = now()->subDay()->toDateString();
        }

        foreach ($loginDates as $date) {
            if ($date === $checkDate) {
                $streak++;
                $checkDate = now()->subDays($streak)->toDateString();
            } else {
                break;
            }
        }

        return $streak;
    }

    private function getPlanName(User $user): string
    {
        $subscription = $user->subscription('default');

        if (! $subscription || $subscription->stripe_status !== 'active') {
            return $user->onTrial() ? 'Trial' : 'Free';
        }

        // Match against plan config
        $plans = config('plans', []);
        foreach ($plans as $key => $plan) {
            $priceIds = array_filter([
                $plan['stripe_price_monthly'] ?? null,
                $plan['stripe_price_annual'] ?? null,
            ]);
            foreach ($priceIds as $priceId) {
                if ($subscription->hasPrice($priceId)) {
                    return $plan['name'] ?? ucfirst($key);
                }
            }
        }

        return 'Subscribed';
    }
}
