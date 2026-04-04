# Billing Review — merge-all session 2026-04-03

**Reviewed by:** v-merge-all consolidation session  
**Date:** 2026-04-03  
**Files reviewed:** app/Http/Controllers/Billing/StripeWebhookController.php, app/Http/Controllers/Billing/SubscriptionController.php, app/Http/Requests/Billing/Concerns/HasCouponValidation.php, app/Http/Requests/Billing/SubscribeRequest.php, app/Http/Requests/Billing/SwapPlanRequest.php, app/Notifications/IncompletePaymentReminder.php, app/Notifications/PaymentActionRequiredNotification.php, app/Notifications/PaymentFailedNotification.php, app/Notifications/PaymentRecoveredNotification.php, app/Services/BillingService.php, resources/js/Pages/Features/Billing.tsx, resources/js/Pages/Settings/Webhooks.tsx

## Review Checklist

- [x] **Test coverage:** CheckoutCouponTest.php covers coupon pass-through (SAVE20 captured), omission (null), invalid format (spaces rejected), and already-subscribed guard. ConcurrencyProtectionTest, SubscriptionCreationTest, SubscriptionPlanSwapTest all pass.
- [x] **Webhook handling:** StripeWebhookController changes are additive (invalidatePlanCache calls on subscription events). No changes to signature verification or event routing logic.
- [x] **Eager loading:** All Cashier mutation methods (cancel, resume, swap, updateQuantity) use `setRelation('owner', $user)` + `loadMissing('items')` + `items->each(setRelation('subscription'))` per CLAUDE.md Critical Gotchas pattern.
- [x] **New method validateCouponCode:** Uses `Cashier::stripe()->coupons->retrieve()`, catches `InvalidRequestException` (invalid/expired coupon), catches generic Exception with warning log. Returns null on success, user-facing string on failure.
- [x] **createCheckoutSession coupon param:** Optional `?string $coupon = null`, applies `discounts: [{coupon: $coupon}]` only when non-null. Backward-compatible (5-param callers unaffected).
- [x] **Redis locks:** validateCouponCode is a read-only Stripe API call — no lock needed. All subscription mutations continue to use BillingService Redis lock pattern.
- [x] **No hardcoded Stripe keys or credentials** in any changed file.
- [x] **PHPStan:** 0 errors on all billing files after fixes.
