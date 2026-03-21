Model: haiku

# Verify Done Report — Session 5667cde6-468e-4370-9b8d-ac6a2b94d3f0

## Checked Files

- `app/Http/Controllers/Billing/SubscriptionController.php`
- `app/Services/BillingService.php`
- `app/Services/PlanLimitService.php`
- `config/features.php`
- `config/plans.php`
- `app/Http/Controllers/Billing/PricingController.php`
- `resources/js/Pages/Pricing.tsx`
- `resources/js/Pages/Billing/Index.tsx`
- `resources/js/lib/events.ts`
- `resources/js/Components/billing/CancelSubscriptionDialog.tsx`
- `routes/web.php`

---

## Findings

### CRITICAL

None detected in convention checks.

---

### HIGH

#### 1. Backend event payload mismatch with frontend EventPropertyMap schema

**File:** `app/Services/PlanLimitService.php:146-150`
**Severity:** HIGH | **Confidence:** HIGH

The backend emits limit threshold events with keys `limit_key` and `current`:
```php
$auditService->logProductEvent($analyticsEvent, $user, [
    'limit_key' => $limitKey,
    'current' => $currentCount,
    'max' => $limit,
]);
```

But the frontend `EventPropertyMap` in `resources/js/lib/events.ts:139-141` declares:
```ts
[AnalyticsEvents.LIMIT_THRESHOLD_50]: { resource: string; current_value: number };
[AnalyticsEvents.LIMIT_THRESHOLD_80]: { resource: string; current_value: number };
[AnalyticsEvents.LIMIT_THRESHOLD_100]: { resource: string; current_value: number };
```

These events are server-side only (never emitted from the frontend), so this is a schema divergence — GA4 queries written against the frontend spec (`resource`, `current_value`) will fail to match actual events (`limit_key`, `current`). **Fix:** Align the frontend EventPropertyMap to match the actual server-side payload, or update the backend to emit `resource` and `current_value`.

---

#### 2. `CancelSubscriptionDialog` does not send `immediately` parameter — dead code branch in controller

**File:** `resources/js/Components/billing/CancelSubscriptionDialog.tsx:52-55`
**Severity:** HIGH | **Confidence:** HIGH

The dialog posts only `reason` and `feedback`. The `immediately` field is never sent, so `$request->validated('immediately', false)` in `SubscriptionController::cancel()` always returns `false`. The immediate-cancellation branch at `SubscriptionController.php:94-95` (`$subscription->cancelNow()`) is dead code from this UI path. If immediate cancellation is not a product feature, remove `immediately` from the Form Request and controller to avoid confusion. If it is intended for admin/API use, document that explicitly.

---

### MEDIUM

#### 3. `billing_period` detection on BILLING_CHECKOUT_COMPLETED uses fragile heuristic

**File:** `resources/js/Pages/Billing/Index.tsx:152`
**Severity:** MEDIUM | **Confidence:** HIGH

```ts
billing_period: (subscription?.priceId?.includes('annual') || subscription?.priceId?.includes('yearly')) ? 'annual' : 'monthly',
```

Stripe price IDs are opaque identifiers and do not reliably contain "annual" or "yearly". The actual billing period was known at checkout initiation but is not propagated through the success redirect. **Fix:** Pass `billing_period` as a URL query param in the `success_url` when creating the Checkout session, then read it on the billing page after redirect.

---

#### 4. `AuditService::log` vs `logProductEvent` inconsistency for subscription lifecycle mutations

**File:** `app/Http/Controllers/Billing/SubscriptionController.php:186, 242, 290, 337, 373`
**Severity:** MEDIUM | **Confidence:** MEDIUM

`subscribe` uses `logProductEvent` (enriched with plan tier, cohort, activation context) while `cancel`, `resume`, `swap`, `updateQuantity`, and `updatePaymentMethod` use the plain `log`. For funnel/churn analysis, enriched context on cancel/resume events is as valuable as on create events. **Fix:** Use `logProductEvent` for all subscription lifecycle mutations, or document the intentional distinction.

---

#### 5. `PlanLimitService::resolveUserPlan` — no eager loading of subscriptions before `$user->subscription('default')`

**File:** `app/Services/PlanLimitService.php:169`
**Severity:** MEDIUM | **Confidence:** MEDIUM

`$user->subscription('default')` is called inside a cached lambda without `subscriptions` being pre-loaded on the user model. Per project CLAUDE.md Critical Gotchas, Cashier methods accessing subscription data trigger N+1 queries when called on a user without eager-loaded subscriptions. This is a correctness/performance concern when `getUserPlan` is called in a loop (e.g., admin list views). **Fix:** Ensure callers of `getUserPlan` eager-load `subscriptions` on the user, or add `$user->loadMissing('subscriptions')` inside `resolveUserPlan`.

---

### LOW

#### 6. `eslint-disable-line` suppressions on React hook dependency arrays

**Files:**
- `resources/js/Pages/Pricing.tsx:99` — `[track]` dependency suppressed
- `resources/js/Pages/Billing/Index.tsx:173` — empty dependency array for URL params effect suppressed

**Severity:** LOW | **Confidence:** HIGH

Both are defensible in context (track is a stable ref; URL param effect is intentionally mount-only). Low risk in practice but diverges from the codebase convention of not suppressing lint rules.

---

#### 7. `PricingController` does not eager-load subscriptions before `getUserPlan`

**File:** `app/Http/Controllers/Billing/PricingController.php:48`
**Severity:** LOW | **Confidence:** MEDIUM

`$this->planLimitService->getUserPlan($user)` calls `$user->subscription('default')` internally without the subscription relationship being loaded on the user. Not a correctness issue for a single-user request, but inconsistent with the codebase's eager-loading discipline.

---

## Agent Review Status

`AGENT_REVIEW_5667cde6-468e-4370-9b8d-ac6a2b94d3f0.md`: EXISTS

The adversarial review identified 3 CRITICAL findings (seat count race condition in checkout, idempotency gaps in createCheckoutSession, missing backend `reason` validation on cancel) and 4 HIGH findings. Those are separate from the convention checks above and should be actioned first.

---

## Summary

| Severity | Count |
|----------|-------|
| CRITICAL | 0     |
| HIGH     | 2     |
| MEDIUM   | 3     |
| LOW      | 2     |

**Overall verdict: CONDITIONALLY PASS**

The billing subsystem follows established patterns correctly: Redis locks on all mutations, proper Cashier eager-loading pattern (`setRelation('owner')` + `loadMissing('items')`), Form Requests for validation, feature-gated routes, cache invalidation after mutations. No TODO/FIXME markers, debug statements, hardcoded secrets, or TypeScript `any` types detected.

Two HIGH findings require attention before production: fix the analytics event payload schema mismatch for limit threshold events, and resolve the dead-code `immediately` parameter in the cancel flow.

The adversarial AGENT_REVIEW contains CRITICAL findings (idempotent checkout, seat count race) that take priority over the convention findings above.
