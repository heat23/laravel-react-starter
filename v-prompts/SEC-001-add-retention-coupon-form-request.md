# SEC-001: Add Form Request to retention coupon endpoint

## Problem
`SubscriptionController::applyRetentionCoupon()` at line 405 accepts a bare `Request` instead of a dedicated Form Request. All other billing mutation endpoints use Form Requests for consistent authorization.

## Fix
Create `ApplyRetentionCouponRequest` and use it in the controller.

## Prompt
```
/v Create app/Http/Requests/Billing/ApplyRetentionCouponRequest.php with authorize() returning true only if the user is authenticated and has an active subscription. Use it in SubscriptionController::applyRetentionCoupon() instead of the bare Request. No validation rules needed since the coupon ID comes from config. Follow the pattern of CancelSubscriptionRequest.
```
