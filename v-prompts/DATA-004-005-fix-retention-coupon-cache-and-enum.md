# DATA-004/005: Fix retention coupon cache invalidation and audit enum

## Problem
1. `SubscriptionController::applyRetentionCoupon()` (line 430) doesn't invalidate admin billing caches after applying a coupon.
2. The audit log at line 433 uses string literal `'retention_coupon_applied'` instead of an `AnalyticsEvent` enum.

## Fix
Add cache invalidation and AnalyticsEvent enum entry.

## Prompt
```
/v Two fixes in billing:
1. Add RETENTION_COUPON_APPLIED case to app/Enums/AnalyticsEvent.php with value 'billing.retention_coupon_applied'.
2. In app/Http/Controllers/Billing/SubscriptionController.php::applyRetentionCoupon(), after the successful applyCoupon() call (line 432), add $this->invalidateAdminCaches() and change the audit log to use AnalyticsEvent::RETENTION_COUPON_APPLIED instead of the string literal.
```
