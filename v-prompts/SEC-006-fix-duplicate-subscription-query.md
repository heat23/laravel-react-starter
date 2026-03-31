# SEC-006: Fix duplicate subscription query in BillingService

## Problem
`BillingService::getSubscriptionStatus()` (line 192) loads the subscription, then calls `resolveUserTier($user)` which loads it again via `$user->subscription('default')`. This creates a redundant database query on every page load via HandleInertiaRequests.

## Fix
Extract tier resolution from the already-loaded subscription object.

## Prompt
```
/v In app/Services/BillingService.php, refactor getSubscriptionStatus() to pass the already-loaded $subscription to resolveTierFromPrice($subscription->stripe_price) instead of calling resolveUserTier($user) which loads the subscription again. The subscription object is already available at line 194. Change line 211 from 'tier' => $this->resolveUserTier($user) to 'tier' => $this->resolveTierFromPrice($subscription->stripe_price) ?? 'free'.
```
