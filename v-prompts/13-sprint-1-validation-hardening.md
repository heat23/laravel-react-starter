# Sprint 1: Validation & Security Hardening (~1 day)
**Source:** audit-full-results_bac592f1.json (2026-04-04)
**Findings:** SEC-001, SEC-002, SEC-004, SEC-006, SEC-007, BIL-001, DATA-001, FE-002

---

## Prompt 1: Create Missing Billing FormRequests (SEC-001, SEC-002)

```
/v Create ResumeSubscriptionRequest and ApplyRetentionCouponRequest FormRequests for the two billing endpoints that use raw Request objects.

1. Create app/Http/Requests/Billing/ResumeSubscriptionRequest.php
   - authorize(): return true (auth middleware handles this)
   - rules(): empty array or minimal (no user-controlled input needed)
   - Follow the pattern from CancelSubscriptionRequest

2. Create app/Http/Requests/Billing/ApplyRetentionCouponRequest.php
   - authorize(): return true
   - rules(): empty array (coupon ID comes from config)

3. Update SubscriptionController::resume() to type-hint ResumeSubscriptionRequest
4. Update SubscriptionController::applyRetentionCoupon() to type-hint ApplyRetentionCouponRequest
5. Write Pest tests verifying both endpoints still work with the new FormRequests
```

---

## Prompt 2: Health Check Query Token Production Guard (SEC-004)

```
/v Add a production safety check for the health check query token setting.

In app/Http/Controllers/HealthCheckController.php, if config('health.allow_query_token') is true AND app()->isProduction(), log a warning:
  Log::warning('HEALTH_ALLOW_QUERY_TOKEN is enabled in production — token may leak via access logs');

Also add this check to AdminConfigController warnings array:
  config('health.allow_query_token') && config('app.env') === 'production'
    ? ['level' => 'warning', 'message' => 'Health check token allowed via query string in production (token may leak in logs)']
    : null

Write a Pest test verifying the warning appears in AdminConfigController output when both conditions are true.
```

---

## Prompt 3: Restrict Webhook URLs to HTTPS (SEC-006)

```
/v Fix CreateWebhookEndpointRequest to only allow HTTPS webhook URLs in production.

In app/Http/Requests/Webhook/CreateWebhookEndpointRequest.php:
- Change 'url:https,http' to: app()->isLocal() ? 'url:https,http' : 'url:https'
- Apply the same change to UpdateWebhookEndpointRequest.php if it has a URL rule

Write a Pest test:
1. In production mode: POST to create webhook with http:// URL -> 422 validation error
2. In local mode: POST to create webhook with http:// URL -> succeeds
```

---

## Prompt 4: Validate Incoming Webhook Provider (SEC-007)

```
/v Add a provider whitelist constraint to the incoming webhook route.

In routes/api.php, on the incoming webhook route:
  Route::post('/{provider}', [IncomingWebhookController::class, 'handle'])
      ->where('provider', 'github|stripe|custom')
      ->middleware(['verify-webhook', 'throttle:120,1'])

Also add a constant to IncomingWebhookController or config/webhooks.php listing supported providers.

Write a Pest test: POST to /api/webhooks/incoming/unknown-provider -> 404
```

---

## Prompt 5: Log Lifecycle Transition Failures (BIL-001)

```
/v Add warning-level logging to the silently swallowed lifecycle transition catch blocks in BillingService.

In app/Services/BillingService.php, find the two catch blocks around LifecycleService::transition() calls (in createSubscription ~line 96 and cancelSubscription ~line 125).

Replace the empty catch bodies with:
  Log::warning('Lifecycle transition failed', [
      'user_id' => $user->id,
      'target_stage' => LifecycleStage::PAYING->value, // or CHURNED
      'error' => $e->getMessage(),
  ]);

No test needed — this is observability improvement only.
```

---

## Prompt 6: Queue Contact Notification (DATA-001)

```
/v Make ContactNotification dispatch asynchronously instead of synchronously in the request lifecycle.

In app/Http/Controllers/ContactController.php, both store() and sales() methods:
1. Change the notification dispatch to queue it:
   (new AnonymousNotifiable)
       ->route('mail', $notifyEmail)
       ->notify(new ContactNotification($submission));
   
   Change ContactNotification to implement ShouldQueue (add the interface and use Queueable trait).

2. If ContactNotification already implements ShouldQueue, verify it does and this is a no-op.

Write a Pest test verifying the notification is queued (use Notification::fake() + assertSentTo with afterCommit check).
```

---

## Prompt 7: Resolve TODO in TwoFactorChallenge (FE-002)

```
/v Find and resolve the TODO/FIXME comment in resources/js/Pages/Auth/TwoFactorChallenge.tsx.

If the TODO references incomplete work, implement it.
If the TODO is stale and the work is done, remove the comment.
Per project conventions, no TODO/FIXME/HACK should remain in delivered code.
```

---

## Summary Checklist

- [ ] ResumeSubscriptionRequest FormRequest created
- [ ] ApplyRetentionCouponRequest FormRequest created
- [ ] Health check query token production warning added
- [ ] Webhook URLs restricted to HTTPS in production
- [ ] Incoming webhook provider whitelist added
- [ ] Lifecycle transition failures logged
- [ ] Contact notification queued
- [ ] TwoFactorChallenge TODO resolved
