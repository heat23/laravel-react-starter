<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\CustomerHealthService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(CustomerHealthService $healthService): Response
    {
        $user = auth()->user();
        $user->loadCount(['settings', 'tokens']);
        // Eager load subscriptions to avoid N+1 from subscribed(), subscription(), onTrial(), billingStatusScore()
        $user->load('subscriptions');

        $stats = [
            'days_since_signup' => (int) $user->created_at->diffInDays(now()),
            'health_score' => $healthService->calculateHealthScore($user),
            'email_verified' => $user->hasVerifiedEmail(),
            'has_subscription' => method_exists($user, 'subscribed') && $user->subscribed('default'),
            'plan_name' => $this->getPlanName($user),
            'settings_count' => $user->settings_count,
            'tokens_count' => $user->tokens_count,
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

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_activity' => $recentActivity,
        ]);
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
