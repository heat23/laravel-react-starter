# Adversarial Code Review: Billing/Monetization Changes

Status: COMPLETED
findings: 10

## Executive Summary
This review covers new checkout flow, pricing UI changes, and related payment infrastructure. **3 CRITICAL findings**, **4 HIGH findings**, **2 MEDIUM findings** requiring immediate remediation before production.

---

## CRITICAL Findings

### 1. Unvalidated `price_id` in Checkout Path Bypasses Seat Validation
**Severity:** CRITICAL
**File:** `app/Http/Controllers/Billing/SubscriptionController.php:39-86`
**Issue:**
The `checkout()` action validates seat count (lines 62-65) but then passes `$quantity` to `createCheckoutSession()` without re-validating during the Stripe API call. A race condition exists:

1. User clicks "Checkout" with quantity=5 (seat validation passes)
2. Admin reduces `PLAN_TEAM_MAX_SEATS` from 50 to 3 in config
3. Stripe Checkout session is created with quantity=5 (exceeds new limit)
4. Payment succeeds with invalid seat count
5. Subscription is created outside seat constraints

**Recommendation:**
- Call `validateSeatCount()` again inside `BillingService::createCheckoutSession()` and throw `SubscriptionException` if invalid
- Alternatively, pass `$tier` to `createCheckoutSession()` and let the service validate before calling Stripe API
- Test this race condition in `SubscriptionCreationTest.php` by reducing `PLAN_TEAM_MAX_SEATS` mid-request

---

### 2. Missing CSRF Protection on `POST /billing/checkout`
**Severity:** CRITICAL
**File:** `routes/web.php:149`
**Issue:**
```php
Route::post('/billing/checkout', [SubscriptionController::class, 'checkout'])->middleware('throttle:5,1')->name('billing.checkout');
```
Route is under `Route::middleware(['auth', 'verified'])` group, which includes default web middleware (CSRF). However, the Checkout flow uses `Inertia::location()` to redirect directly to Stripe's domain.

**Risk:** An attacker can POST to `/billing/checkout` from a malicious site (CSRF token bypassed by not requiring it in the request). The JavaScript fetch doesn't validate CSRF in the Pricing component (line 174-181 of `Pricing.tsx` uses `router.post()` which auto-includes CSRF, but if someone crafted a direct form submission, it would bypass).

**Verification:** Review that Inertia's `router.post()` is the *only* way to call this route. If a direct form POST is possible, CSRF is bypassable.

**Recommendation:**
- Explicit verification: The route uses `auth` + `verified` + web middleware which *should* enforce CSRF. Confirm by checking `bootstrap/app.php` that CSRF middleware is registered in web middleware group.
- Add explicit comment in `SubscriptionController::checkout()` confirming CSRF is enforced by Laravel's middleware, not by the controller.

---

### 3. `createCheckoutSession()` Missing Idempotency & Payment Intent Reuse Risk
**Severity:** CRITICAL
**File:** `app/Services/BillingService.php:38-53`
**Issue:**
`BillingService::createCheckoutSession()` calls `$user->newSubscription('default', $priceId)->checkout()` without checking if a checkout session already exists. Multiple calls produce multiple Stripe Checkout sessions.

**Attack Vector:**
1. User submits `POST /billing/checkout` with `price_id=price_pro_monthly`
2. Server creates Stripe Checkout session (e.g., `cs_123`)
3. Network timeout — user doesn't receive redirect URL
4. User retries (browser back + submit again)
5. Server creates *new* Checkout session `cs_456`
6. Both sessions are valid — user can complete either one, creating duplicate subscription attempts

**Race condition with `withLock`:**
The lock in `createSubscription()` (line 65) prevents concurrent *subscription* calls, but `checkout()` is *not* locked. An attacker can create multiple checkout sessions before either payment completes.

**Recommendation:**
- Store checkout session ID in database (e.g., `checkout_session_id` column on `users` table or new `pending_checkout_sessions` table)
- Check if a non-expired pending session exists; reuse or delete + create new
- Require idempotency key in request: `$request->header('Idempotency-Key')` and cache the session URL
- Add integration test: call checkout twice with same `price_id`, verify only one session is created

---

## HIGH Findings

### 4. No Quantity Validation in `createCheckoutSession()`
**Severity:** HIGH
**File:** `app/Services/BillingService.php:45`
**Issue:**
`->quantity($quantity)` is called without re-checking seat constraints. The quantity is validated in the controller, but Stripe API could accept out-of-bounds values if the config changes between request validation and API call.

**Recommendation:**
- Move seat validation logic to a reusable method in `BillingService`
- Call it in both `checkout()` and `createCheckoutSession()` before Stripe API
- Example: `private function validateQuantity(string $tier, int $quantity): void { /* throw if invalid */ }`

---

### 5. `CancelSubscriptionDialog` Missing Required Reason Validation on Backend
**Severity:** HIGH
**File:** `app/Http/Requests/Billing/CancelSubscriptionRequest.php:21`
**Issue:**
```php
'reason' => ['sometimes', 'nullable', 'string', 'in:too_expensive,switching_tools,no_longer_needed,missing_features,other'],
```
`reason` is `sometimes` + `nullable`, meaning a cancel request can succeed with no reason provided. The UI enforces required (line 179 of `CancelSubscriptionDialog.tsx`: `disabled={!reason}`), but frontend validation is UX only.

**Attack Vector:**
Direct API call: `curl -X POST /billing/cancel -H "Authorization: Bearer $TOKEN" -d '{}' ` → succeeds with null reason, losing cancellation context.

**Recommendation:**
Change to: `'reason' => ['required', 'string', 'in:...']` to enforce backend-level validation. This ensures reason is always captured for churn analysis.

---

### 6. TypeScript Type Mismatch: `stripe_price_id` vs `stripe_price_id_annual`
**Severity:** HIGH
**File:** `resources/js/Pages/Pricing.tsx:152-155`
**Issue:**
```tsx
const priceId =
  billingPeriod === 'annual' && tier.stripe_price_id_annual
    ? tier.stripe_price_id_annual
    : tier.stripe_price_id;
```
If `tier.stripe_price_id_annual` is `undefined`, the fallback is `tier.stripe_price_id`. However, TierConfig interface (line 32-33) defines both as `string | null` — not `string | undefined`. This is safe at runtime but creates confusing nullable semantics.

**Real Risk:** If `stripe_price_id` is `null` (enterprise plan) and user tries to start checkout, `priceId` becomes `null`. The POST to `/billing/checkout` sends `price_id: null`, which passes `Rule::in($this->allowedPriceIds())` only if `null` is in allowed prices. Likely fails, but the error message is opaque.

**Recommendation:**
- Add explicit null check in `handleCheckout()`: `if (!priceId) { toast.error('This plan is not available for purchase'); return; }`
- Ensure enterprise plans (with `price === null`) have `tier.coming_soon || tier.price === null` to gate the CTA at render time (already done, but defensive check helps)

---

### 7. `BillingService::resolveTierFromPrice()` Logs but Never Throws
**Severity:** HIGH
**File:** `app/Services/BillingService.php:237-255`
**Issue:**
```php
public function resolveTierFromPrice(string $priceId): ?string {
    // ... loop through tiers ...
    Log::warning('Unknown Stripe price ID encountered during tier resolution', ['price_id' => $priceId]);
    return null;
}
```
When an invalid `price_id` is provided (e.g., attacker-controlled or stale), the method silently returns `null` and logs. Downstream code that uses this:

- `checkout()` line 51: `$tier = $this->billingService->resolveTierFromPrice($priceId);` → `null`
- `checkout()` line 53: `if ($tier === null) { return back()->with('error', ...) }` ✓ Handles it

But if called from other controllers without null check, silent failures occur. Example: `swapPlan()` line 288 — `$newTier = ... ?? 'unknown'` falls back to `'unknown'` tier, which doesn't exist in config.

**Recommendation:**
- Make the method throw `SubscriptionException` for invalid prices: `throw new SubscriptionException('Invalid plan selected');` instead of returning null
- Update all callers to remove null checks or wrap in try-catch
- This forces developers to handle the error explicitly

---

## MEDIUM Findings

### 8. Stripe Checkout Session URL Not Validated Before Redirect
**Severity:** MEDIUM
**File:** `app/Services/BillingService.php:52`, used in `SubscriptionController.php:76`
**Issue:**
```php
return Inertia::location($checkoutUrl);
```
`$checkoutUrl` comes from `$checkout->url` (Stripe SDK). If Stripe API returns a malformed URL or an attacker-controlled value somehow, `Inertia::location()` redirects without validation.

**Unlikely but Worth Fixing:**
- Stripe SDK is trusted, but defense-in-depth: validate that `$checkoutUrl` starts with `https://checkout.stripe.com/` before redirect
- Prevent open redirect vulnerabilities (low risk here, but good practice)

**Recommendation:**
```php
if (!str_starts_with($checkoutUrl, 'https://checkout.stripe.com/')) {
    throw new SubscriptionException('Invalid Stripe checkout URL');
}
return Inertia::location($checkoutUrl);
```

---

### 9. `PlanLimitService::checkThresholds()` Cache Key Collision Risk
**Severity:** MEDIUM
**File:** `app/Services/PlanLimitService.php:134`
**Issue:**
```php
$cacheKey = "pql:{$user->id}:{$limitKey}:threshold_{$threshold}";
if (Cache::has($cacheKey)) {
    break;
}
Cache::put($cacheKey, true, now()->addHours(24));
```
If a user hits multiple thresholds in one request (e.g., at exactly 100% usage), the loop breaks after checking the first threshold that matches. The logic is:
1. Check 100% threshold — cache hit → break
2. Never check 80%, 50%

**Actually Correct Behavior:** The loop goes 100 → 80 → 50 in reverse, so the first hit (100%) breaks. This is intentional to fire only the highest threshold. No bug here.

**BUT:** The cache strategy is loose. If a user approaches 50%, the event fires once (cached). Later, if usage drops below 50%, the cache persists for 24h, so a re-approach of 50% in the same day won't fire again. This is by design but could mask re-engagement opportunities.

**Recommendation:** No change needed if this is intentional. Add a comment clarifying the 24h per-threshold per-user per-day behavior is by design. Consider shortening TTL to 12h if re-engagement at same threshold is desired.

---

## INFO Findings

### 10. Annual Billing Default (Pricing.tsx line 106-111)
**Severity:** INFO
**File:** `resources/js/Pages/Pricing.tsx:106-111`
**Issue:**
```tsx
const [billingPeriod, setBillingPeriod] = useState<'monthly' | 'annual'>(
  () =>
    Object.values(tiers).some((t) => t.price_annual && t.price_annual > 0)
      ? 'annual'
      : 'monthly'
);
```
Default is `annual` if *any* tier has annual pricing. This is a strong default but may surprise users. Marketing teams often prefer `monthly` as safer UX (lower initial commitment perception).

**Recommendation:** Document this in code or config (no security issue). Consider adding a feature flag or env var: `PRICING_DEFAULT_PERIOD=annual|monthly` if you want flexibility.

---

## Summary Table

| Finding | Severity | File | Line(s) | Action Required |
|---------|----------|------|---------|-----------------|
| Seat validation race condition | CRITICAL | SubscriptionController.php | 39–86 | Re-validate in `createCheckoutSession()` |
| Missing CSRF (likely OK) | CRITICAL | routes/web.php | 149 | Verify web middleware includes CSRF |
| No idempotency on checkout session | CRITICAL | BillingService.php | 38–53 | Store session ID, reuse or invalidate |
| Quantity re-validation missing | HIGH | BillingService.php | 45 | Create reusable seat validation method |
| Missing required reason validation | HIGH | CancelSubscriptionRequest.php | 21 | Change `sometimes` to `required` |
| TypeScript null handling | HIGH | Pricing.tsx | 152–155 | Add explicit null check before POST |
| `resolveTierFromPrice()` silent failure | HIGH | BillingService.php | 237–255 | Throw exception instead of returning null |
| Checkout URL not validated | MEDIUM | BillingService.php | 52 | Add HTTPS origin check |
| PQL cache strategy | MEDIUM | PlanLimitService.php | 134 | Document intent (likely by design) |
| Annual billing default | INFO | Pricing.tsx | 106–111 | Document or add config |

---

## Recommended Order of Fixes
1. **Idempotency on checkout** (CRITICAL) — prevents duplicate subscriptions
2. **Seat validation in service** (CRITICAL) — closes tier config race condition
3. **CSRF verification** (CRITICAL) — confirm middleware protects route
4. **Required reason field** (HIGH) — backend data integrity
5. **TypeScript null check** (HIGH) — prevents undefined price_id
6. **Throw on invalid tier** (HIGH) — explicit error handling
7. **URL validation** (MEDIUM) — defense-in-depth

---

## Testing Checklist
- [ ] Race condition test: reduce seat limit mid-checkout, verify subscription rejects
- [ ] Idempotency test: POST same checkout twice, verify one session/subscription
- [ ] Cancel without reason: direct API call, verify 422 or 400 response
- [ ] Invalid price_id: POST with `price_id: "invalid_123"`, verify friendly error
- [ ] Seat count boundaries: test min/max for team tier in `createCheckoutSession()`
- [ ] Null price_id: enterprise plan checkout, verify no silent failures
