<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductAnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class AdminProductAnalyticsController extends Controller
{
    public function __invoke(ProductAnalyticsService $analytics): Response
    {
        return Inertia::render('Admin/ProductAnalytics', [
            'signup_trend' => $analytics->getSignupTrend(7),
            'onboarding_funnel' => $analytics->getOnboardingFunnelConversion(),
            'activation' => $analytics->getOnboardingCompletionRate(),
            'feature_adoption' => $analytics->getFeatureAdoptionByWeek(),
            'subscription_events' => $analytics->getSubscriptionEvents(),
        ]);
    }
}
