# PERF-002: Cache subscription status per-user

## Problem
`HandleInertiaRequests::share()` loads subscription data on every Inertia request when billing is enabled. This adds 1-2 queries per page load.

## Fix
Cache subscription status with short TTL.

## Prompt
```
/v In app/Http/Middleware/HandleInertiaRequests.php, cache the subscription status in the auth.user.subscription closure. Use cache()->remember("user:{$user->id}:subscription_status", 60, fn() => ...) around the billingService->getSubscriptionStatus() call. Invalidate this cache key in CacheInvalidationManager::invalidateUser() and after any subscription mutation in SubscriptionController.
```
