<?php

namespace App\Http\Controllers;

use App\Services\CustomerHealthService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(CustomerHealthService $healthService): Response
    {
        $user = auth()->user();

        $stats = [
            'days_since_signup' => (int) $user->created_at->diffInDays(now()),
            'health_score' => $healthService->calculateHealthScore($user),
            'email_verified' => $user->hasVerifiedEmail(),
            'has_subscription' => method_exists($user, 'subscribed') && $user->subscribed('default'),
            'plan_name' => $this->getPlanName($user),
            'settings_count' => $user->settings()->count(),
            'tokens_count' => $user->tokens()->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }

    private function getPlanName(mixed $user): ?string
    {
        if (! method_exists($user, 'subscription')) {
            return null;
        }

        $subscription = $user->subscription('default');

        if (! $subscription || $subscription->stripe_status !== 'active') {
            return $user->onTrial() ? 'Trial' : 'Free';
        }

        // Match against plan config
        $plans = config('plans', []);
        foreach ($plans as $key => $plan) {
            $priceIds = array_filter([
                $plan['stripe_monthly_price_id'] ?? null,
                $plan['stripe_yearly_price_id'] ?? null,
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
