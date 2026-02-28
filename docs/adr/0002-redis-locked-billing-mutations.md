# ADR 0002: Redis-Locked Billing Mutations

## Status
Accepted

## Context
Stripe subscription operations (create, cancel, resume, swap, updateQuantity) are multi-step: they modify both the local database and the remote Stripe API. Without protection, concurrent requests from the same user (e.g., double-clicking "Cancel") can produce race conditions — two Stripe API calls hitting the same subscription simultaneously, leading to inconsistent state.

## Decision
All subscription mutations go through `BillingService`, which wraps each operation in:
1. A Redis lock (35s timeout = 30s Stripe API timeout + 5s buffer)
2. A database transaction for local atomicity
3. Mandatory eager loading of `owner` and `items.subscription` before any Cashier method call

If the lock cannot be acquired, a `ConcurrentOperationException` is thrown immediately (fail-fast, no queuing).

## Consequences

### Positive
- Race conditions are impossible for same-user concurrent requests
- Eager loading prevents N+1 queries and "property on null" errors from lazy loading
- Exception is catchable, allowing controllers to show user-friendly "try again" messages

### Negative
- Requires Redis in production (database cache driver works for dev but lacks true locking)
- 35s lock timeout means a stuck Stripe API call blocks the user for 35s before the lock expires
- All billing code must go through BillingService — direct Cashier calls bypass the lock

### Testing Requirements
- `ConcurrencyProtectionTest` verifies lock acquisition, rejection, and release
- `BillingServiceIntegrationTest` verifies eager loading patterns
- `BillingServiceTest` verifies each operation delegates correctly

## References
- `app/Services/BillingService.php` — implementation
- `app/Exceptions/ConcurrentOperationException.php` — fail-fast exception
- `tests/Feature/Billing/ConcurrencyProtectionTest.php` — lock behavior tests
