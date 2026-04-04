# Sprint 2: Billing & CI Pipeline Hardening (~2-3 days)
**Source:** audit-full-results_bac592f1.json (2026-04-04)
**Findings:** SEC-003, SEC-008, BIL-002, BIL-003, TEST-001, INFRA-001-004

---

## Prompt 1: Password Confirmation on Billing Mutations (SEC-003)

```
/v Add password confirmation to destructive billing routes.

In routes/web.php, add the password.confirm middleware to these billing routes:
- billing.cancel
- billing.swap
- billing.quantity (seat changes)

Do NOT add password.confirm to:
- billing.subscribe (first-time, user just logged in)
- billing.checkout (redirects to Stripe)
- billing.resume (restoring a cancellation is low-risk)
- billing.retention-coupon (already rate-limited to 3/hour)

Implementation:
1. Add 'password.confirm' middleware to the 3 routes listed above
2. On the React side, the password.confirm middleware will redirect to the password confirmation page — verify this works with Inertia's redirect handling
3. Write Pest tests:
   - Cancel without confirmed password -> redirect to password.confirm
   - Cancel with confirmed password (session flag) -> succeeds
   - Same pattern for swap and quantity
```

---

## Prompt 2: Stop Exposing Webhook Secrets in Show Endpoint (SEC-008)

```
/v Remove webhook secret from the show() and index() API responses. Only return it once at creation time.

In app/Http/Controllers/Api/WebhookEndpointController.php:
1. In show(), replace 'secret' => $endpoint->secret with 'secret_last4' => '****' . substr($endpoint->secret, -4)
2. In index(), the secret is already not included — verify this
3. The store() method already returns the full secret at creation time — keep this
4. Add a new regenerateSecret() endpoint:
   POST /api/webhooks/{endpointId}/regenerate-secret
   - Generates a new secret via WebhookService::generateSecret()
   - Returns the new secret once
   - Rate limited to 5/min
   - Add route in routes/api.php

Write Pest tests:
- GET /api/webhooks/{id} -> response does NOT contain full secret, contains secret_last4
- POST /api/webhooks/{id}/regenerate-secret -> returns new full secret
```

---

## Prompt 3: Prevent Repeated Retention Coupon Application (BIL-003)

```
/v Add a check to prevent users from applying the retention coupon more than once.

In app/Http/Controllers/Billing/SubscriptionController.php::applyRetentionCoupon():
1. Before applying the coupon, check if the subscription already has a coupon:
   - Query the Stripe subscription to check for existing discounts
   - OR track application in user_settings: UserSetting::getValue($user->id, 'retention_coupon_applied')
2. If already applied, return error: "Discount has already been applied to your subscription."
3. After successful application, store the flag: UserSetting::setValue($user->id, 'retention_coupon_applied', true)

Write Pest tests:
- First application -> success
- Second application -> 422 "already applied"
```

---

## Prompt 4: Remove Global CSRF Middleware Disable from TestCase (TEST-001)

```
/v Remove the global CSRF middleware disable from tests/TestCase.php and fix any tests that break.

1. Remove this line from tests/TestCase.php:
   $this->withoutMiddleware(ValidateCsrfToken::class);

2. Run the full test suite: php artisan test --parallel
3. For each failing test:
   - If the test should bypass CSRF (most POST/PATCH/DELETE tests): add ->withoutMiddleware(ValidateCsrfToken::class) to that specific test
   - Better: use $this->actingAs($user) which automatically handles session/CSRF in Laravel test helpers

4. Add CSRF verification tests for critical routes:
   it('requires CSRF token on billing cancel', function () {
       // POST without CSRF token should return 419
   });

Note: Most Laravel test helpers (post(), patch(), delete()) handle CSRF automatically when using actingAs(). The global disable may have been added unnecessarily. Test without it first.
```

---

## Prompt 5: CI Pipeline Hardening (INFRA-001, INFRA-002, INFRA-003, INFRA-004)

```
/v Harden the CI pipeline in .github/workflows/ci.yml with these changes:

1. INFRA-001: Run code-quality on push to main too
   Change: if: github.event_name == 'pull_request'
   To: remove the `if` condition entirely (runs on both push and PR)

2. INFRA-002: Make high-severity npm audit blocking
   Change: npm audit --audit-level=high with continue-on-error: true
   To: npm audit --audit-level=high (remove continue-on-error)

3. INFRA-003: Make JS coverage step blocking
   Remove continue-on-error: true from the JS test coverage step

4. INFRA-004: Add Dependabot configuration
   Create .github/dependabot.yml:
   version: 2
   updates:
     - package-ecosystem: "composer"
       directory: "/"
       schedule:
         interval: "weekly"
       reviewers:
         - "sood"
     - package-ecosystem: "npm"
       directory: "/"
       schedule:
         interval: "weekly"
       reviewers:
         - "sood"
     - package-ecosystem: "github-actions"
       directory: "/"
       schedule:
         interval: "monthly"

No tests needed for CI config changes — verify by pushing to a branch and checking Actions.
```

---

## Prompt 6: Lock Checkout Session Creation (BIL-002)

```
/v Wrap BillingService::createCheckoutSession() in the withLock() pattern for consistency.

In app/Services/BillingService.php::createCheckoutSession():
1. Wrap the existing logic in: return $this->withLock($this->lockKey('checkout', $user), function () use (...) { ... });
2. This prevents concurrent checkout sessions for the same user

Write a Pest test:
- Verify checkout session is created successfully (existing test should still pass)
- Optionally: test concurrent calls return ConcurrentOperationException
```

---

## Summary Checklist

- [ ] Password confirmation on cancel/swap/quantity routes
- [ ] Webhook secret masked in show endpoint
- [ ] Webhook secret regenerate endpoint added
- [ ] Retention coupon repeat-application prevented
- [ ] Global CSRF disable removed from TestCase
- [ ] CSRF verification tests added
- [ ] Code quality runs on push to main
- [ ] npm audit high-severity blocking
- [ ] JS coverage step blocking
- [ ] Dependabot configured
- [ ] Checkout session Redis-locked
