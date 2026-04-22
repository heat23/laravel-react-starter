<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEvent;
use App\Enums\PlanTier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSubscriptionIndexRequest;
use App\Models\AuditLog;
use App\Services\AdminBillingStatsService;
use App\Services\AuditService;
use App\Services\BillingService;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private AdminBillingStatsService $statsService,
        private AuditService $auditService,
    ) {}

    public function dashboard(): Response
    {
        $recentEvents = AuditLog::where(function ($q) {
            $q->where('event', 'like', 'billing.%')
                ->orWhere('event', 'like', 'subscription.%');
        })
            ->with('user')
            ->latest()
            ->limit(config('pagination.admin.recent_events', 10))
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
            'cohort_retention' => $this->statsService->getCohortRetention(),
        ]);
    }

    public function subscriptions(AdminSubscriptionIndexRequest $request): Response
    {
        $this->auditService->log(AuditEvent::ADMIN_BILLING_SUBSCRIPTIONS_VIEWED, [
            'filters' => $request->validated(),
        ]);

        return Inertia::render('Admin/Billing/Subscriptions', [
            'subscriptions' => $this->statsService->getFilteredSubscriptions($request->validated()),
            'filters' => $request->only('search', 'status', 'tier', 'sort', 'dir'),
            'statuses' => ['active', 'trialing', 'past_due', 'canceled', 'incomplete', 'incomplete_expired'],
            'tiers' => collect(config('plans.tier_hierarchy', []))->filter(fn ($t) => $t !== 'free')->values()->all(),
        ]);
    }

    public function show(Subscription $subscription): Response
    {
        $subscription->load(['owner' => fn ($q) => $q->withTrashed(), 'items']);

        $this->auditService->log(AuditEvent::ADMIN_BILLING_SUBSCRIPTION_VIEWED, [
            'subscription_id' => $subscription->id,
            'subject_user_id' => $subscription->user_id,
        ]);

        $items = $subscription->items->map(fn ($item) => [
            'id' => $item->id,
            'stripe_price' => $item->stripe_price,
            'stripe_product' => $item->stripe_product,
            'quantity' => $item->quantity,
            'tier' => PlanTier::safeValue($this->billingService->resolveTierFromPrice($item->stripe_price)),
        ]);

        $auditLogs = AuditLog::where('user_id', $subscription->user_id)
            ->where(function ($q) {
                $q->where('event', 'like', 'billing.%')
                    ->orWhere('event', 'like', 'subscription.%');
            })
            ->latest()
            ->limit(config('pagination.admin.subscription_logs', 20))
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
                    ? PlanTier::safeValue($this->billingService->resolveTierFromPrice($items->first()['stripe_price']))
                    : PlanTier::Free->value,
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
                'ends_at' => $subscription->ends_at?->toISOString(),
                'created_at' => $subscription->created_at?->toISOString(),
            ],
            'items' => $items,
            'audit_logs' => $auditLogs,
        ]);
    }

    public function export(AdminSubscriptionIndexRequest $request): StreamedResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_SUBSCRIPTIONS_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = $this->statsService->buildSubscriptionQuery($request->validated());
        $maxRows = config('pagination.export.max_rows', 10000);
        $billingService = $this->billingService;

        return response()->streamDownload(function () use ($query, $maxRows, $billingService) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility
            fputcsv($handle, ['User Name', 'User Email', 'Tier', 'Status', 'Quantity', 'Trial Ends', 'Ends At', 'Created At', 'Stripe ID']);

            $exported = 0;
            foreach ($query->lazyById(500, 'subscriptions.id') as $row) {
                if ($exported >= $maxRows) {
                    break;
                }
                fputcsv($handle, array_map(
                    fn ($v) => is_string($v) && isset($v[0]) && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"])
                        ? "'".$v
                        : $v,
                    [
                        $row->user_name,
                        $row->user_email,
                        PlanTier::safeValue($billingService->resolveTierFromPrice($row->item_price)),
                        $row->stripe_status,
                        $row->quantity,
                        $row->trial_ends_at,
                        $row->ends_at,
                        $row->created_at,
                        $row->stripe_id,
                    ]
                ));
                $exported++;
            }

            fclose($handle);
        }, 'subscriptions-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
