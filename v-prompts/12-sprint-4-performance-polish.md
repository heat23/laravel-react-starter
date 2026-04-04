# Sprint 4: Performance & Polish

**Source:** `AUDIT_FULL_REPORT_76c2377f-e028-444b-9943-bc2712a067f3.md`
**Priority:** P2 — Post-Launch
**Estimated Effort:** 3-4 days

---

## Task 1: PlanLimitService Cache TTL Increase (PERF-HIGH-007)

```
/v fix PERF-HIGH-007: In app/Services/PlanLimitService.php lines 87-94, getUserPlan() cache TTL is only 10 seconds, causing cache stampedes on high-traffic users. Fix:
1. Increase PLAN_CACHE_TTL to 3600 (1 hour)
2. Add proactive cache invalidation in BillingService after subscribe/cancel/swap/resume — call Cache::forget("user_plan:{$userId}")
3. Add Cache::lock() around the refresh to serialize concurrent rebuilds during cache miss
4. Document the invalidation contract in CLAUDE.md Critical Gotchas section
Write Pest tests verifying: cache is used on second call, cache is invalidated after subscription change, concurrent requests don't duplicate queries.
```

## Task 2: Per-Subscription Webhook Event Ordering (SEC-MED-009)

```
/v fix SEC-MED-009: In app/Http/Controllers/Billing/StripeWebhookController.php lines 46-78, last_webhook_at is tracked per-user globally. Events for different subscriptions are incorrectly rejected as out-of-order. Fix:
1. Track last_webhook_at per subscription ID (e.g., cache key "stripe_webhook:{subscription_id}:last_at")
2. Use Stripe event ID for true deduplication (cache "stripe_event:{event_id}" for 24 hours)
3. Log rejected out-of-order events at warning level for manual review
Write Pest tests for: events from different subscriptions processed independently, duplicate event ID rejected, out-of-order events for same subscription rejected.
```

## Task 3: Align Grace Period with Stripe Dunning (SEC-MED-010)

```
/v fix SEC-MED-010: In app/Services/PlanLimitService.php lines 275-287, the 7-day grace period from updated_at may not match Stripe's actual retry schedule. Fix:
1. Instead of hardcoded grace from updated_at, check subscription->asStripeSubscription()->latest_invoice->payment_intent->status when resolving plan during past_due
2. If Stripe shows payment_intent succeeded, treat as active regardless of local status
3. Add a scheduled command to sync past_due subscriptions with Stripe (run hourly)
4. Keep the config-based grace period as fallback when Stripe API is unavailable
```

## Task 4: QR Code Dark Mode Fix (UX-MED-019)

```
/v fix UX-MED-019: In resources/js/Pages/Settings/Security.tsx line 170, QR code container uses dark:bg-white which makes QR unreadable. The QR code itself is rendered as SVG. Fix: Keep the QR code on white background (required for scanning) but add a visible border/shadow in dark mode for contrast. Change to: className="... bg-white p-4 dark:ring-1 dark:ring-white/20 dark:shadow-lg"
```

## Task 5: Keyboard Navigation & Empty States

```
/v fix UX-MED-020 and UX-MED-021:
1. Add keyboard shortcuts help tooltip to admin layout (? key to show shortcuts legend)
2. In resources/js/Pages/Settings/ApiTokens.tsx and Webhooks.tsx, add EmptyState component when data arrays are empty (follow Dashboard empty state pattern)
3. Ensure all dialog/modal components trap focus correctly (verify with Tab cycling test)
```

## Task 6: PHPStan Baseline & Pre-Commit Hooks

```
/v fix DEVOPS-MED-013 and DEVOPS-MED-014:
1. In phpstan.neon, set reportUnmatchedIgnoredErrors: true to catch stale baseline entries
2. Run vendor/bin/phpstan analyse and remove any baseline entries that no longer match
3. In .husky/pre-commit, uncomment ESLint check but make it non-blocking: npx eslint . || echo "ESLint issues found - run npm run lint to fix"
4. Uncomment tsc --noEmit check similarly
```

## Task 7: N+1 Query Fixes

```
/v fix PERF-MED-004 and PERF-MED-005:
1. In app/Models/AuditLog.php, add scopeWithUser that eager-loads user relationship. Update all controllers that query audit logs to use this scope.
2. In app/Services/WebhookService.php line 17, change endpoint query from loading all + filtering in memory to: WebhookEndpoint::where('user_id', $userId)->where('active', true)->get()->filter(...)
3. Add query count assertion tests for audit log listing and webhook dispatch.
```
