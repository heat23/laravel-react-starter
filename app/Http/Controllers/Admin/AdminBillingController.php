<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSubscriptionIndexRequest;
use App\Models\AuditLog;
use App\Services\AdminBillingStatsService;
use App\Services\BillingService;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;

class AdminBillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private AdminBillingStatsService $statsService,
    ) {}

    public function dashboard(): Response
    {
        $recentEvents = AuditLog::where(function ($q) {
            $q->where('event', 'like', 'billing.%')
                ->orWhere('event', 'like', 'subscription.%');
        })
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'event' => $log->event,
                'user_name' => $log->user?->name,
                'metadata' => $log->metadata,
                'created_at' => $log->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/Billing/Dashboard', [
            'stats' => $this->statsService->getDashboardStats(),
            'tier_distribution' => $this->statsService->getTierDistribution(),
            'status_breakdown' => $this->statsService->getStatusBreakdown(),
            'growth_chart' => $this->statsService->getGrowthChart(),
            'trial_stats' => $this->statsService->getTrialStats(),
            'recent_events' => $recentEvents,
        ]);
    }

    public function subscriptions(AdminSubscriptionIndexRequest $request): Response
    {
        return Inertia::render('Admin/Billing/Subscriptions', [
            'subscriptions' => $this->statsService->getFilteredSubscriptions($request->validated()),
            'filters' => $request->only('search', 'status', 'tier', 'sort', 'dir'),
            'statuses' => ['active', 'trialing', 'past_due', 'canceled', 'incomplete', 'incomplete_expired'],
            'tiers' => ['pro', 'team', 'enterprise'],
        ]);
    }

    public function show(Subscription $subscription): Response
    {
        $subscription->load(['owner' => fn ($q) => $q->withTrashed(), 'items']);

        $items = $subscription->items->map(fn ($item) => [
            'id' => $item->id,
            'stripe_price' => $item->stripe_price,
            'stripe_product' => $item->stripe_product,
            'quantity' => $item->quantity,
            'tier' => $this->billingService->resolveTierFromPrice($item->stripe_price),
        ]);

        $auditLogs = AuditLog::where('user_id', $subscription->user_id)
            ->where(function ($q) {
                $q->where('event', 'like', 'billing.%')
                    ->orWhere('event', 'like', 'subscription.%');
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'event' => $log->event,
                'metadata' => $log->metadata,
                'created_at' => $log->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/Billing/Show', [
            'subscription' => [
                'id' => $subscription->id,
                'user_name' => $subscription->owner?->name ?? '[Deleted User]',
                'user_email' => $subscription->owner?->email ?? '',
                'user_id' => $subscription->user_id,
                'stripe_id' => $subscription->stripe_id,
                'stripe_status' => $subscription->stripe_status,
                'tier' => $items->isNotEmpty()
                    ? $this->billingService->resolveTierFromPrice($items->first()['stripe_price'])
                    : 'free',
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
                'ends_at' => $subscription->ends_at?->toISOString(),
                'created_at' => $subscription->created_at?->toISOString(),
            ],
            'items' => $items,
            'audit_logs' => $auditLogs,
        ]);
    }
}
