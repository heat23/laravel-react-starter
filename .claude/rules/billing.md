---
description: Billing domain gotchas — Cashier eager loading, Redis locks, seat constraints
globs:
  - app/Services/Billing*
  - app/Http/Controllers/Billing/**
  - app/Jobs/CancelOrphanedStripeSubscription*
  - tests/**/Billing/**
---

# Billing Gotchas (DO NOT MODIFY WITHOUT READING)

**Why eager loading is required:** Cashier methods like `cancel()` and `swap()` internally access `$subscription->owner` and nested `$subscription->items->subscription` relationships. Without eager loading, each call triggers lazy loading queries, causing N+1 problems and potential race conditions.

**Detection rule:** If you're calling ANY Cashier method (`cancel`, `resume`, `swap`, `updateQuantity`, `noProrate`, `anchorBillingCycleOn`), you MUST eager load first: `$subscription->load('owner', 'items.subscription')`

**Error symptom:** `Attempt to read property "stripe_id" on null` when calling `->cancel()` means `owner` wasn't loaded.

**Pattern to follow:** See `app/Services/BillingService.php` lines 68-70 for correct eager loading pattern.

**Billing route middleware:** `LoadBillingContext` middleware is applied to the billing route group in `routes/web.php`. It calls `$user->loadMissing('subscriptions.items')` once per request. Do NOT add per-method `loadMissing` calls in billing controllers — the middleware handles it.

**Redis locks:** All subscription mutations MUST use `BillingService` methods — direct Cashier calls will cause race conditions. Redis locks (35s timeout) prevent concurrent operations. If lock acquisition fails, operation is rejected with `ConcurrentOperationException`.

**Seat constraints:** Team/Enterprise tiers have min 1, max 50 seats for team tier — validate before subscription creation.

**Billing (Production-Grade):**
- `BillingService` — Redis-locked subscription mutations (create, cancel, resume, swap)
- Billing controllers — four focused controllers in `app/Http/Controllers/Billing/`:
  - `SubscriptionCheckoutController` — checkout session, legacy subscribe, billing portal
  - `SubscriptionLifecycleController` — cancel, resume, swapPreview, swap, updateQuantity
  - `PaymentMethodController` — updatePaymentMethod
  - `RetentionController` — applyRetentionCoupon
  - Shared helpers (`friendlyStripeError`, `invalidateAdminCaches`) via `HandlesBillingErrors` trait
- Plan tiers: `App\Enums\PlanTier` (Free, Pro, ProTeam, Team, Enterprise) — never use raw strings like `'pro'` or `'team'` in billing code. Use `PlanTier::Pro`, `PlanTier::Free`, etc. Serialize for Inertia/JSON with `->value`.
- `BillingService::resolveUserTier()` returns `PlanTier`. `resolveTierFromPrice()` returns `?PlanTier`. `validateSeatCount()` accepts `PlanTier`.
- `PlanLimitService::getUserPlan()` returns `PlanTier`. `getNextTier()` accepts and returns `PlanTier`.
- Incomplete payment tracking: `subscriptions:check-incomplete` command sends reminders at 1h/12h
