---
title: How We Use Redis Locks to Prevent Double-Charges in Laravel Stripe Billing
slug: laravel-redis-locks-stripe-billing
description: Concurrent subscription requests can cause duplicate Stripe charges. Here's the Redis lock pattern we use in Laravel to make subscription mutations safe.
date: 2026-03-10
readingTime: 8 min read
tags: [Laravel, Stripe, Redis, billing]
---

## The Problem: Concurrent Subscription Mutations

Imagine two browser tabs open simultaneously, both clicking "Upgrade to Pro." Without protection, your Laravel app sends two `$subscription->create()` calls to Stripe within milliseconds of each other. Stripe creates two subscriptions. Your customer is charged twice.

This isn't hypothetical. It happens in production when users double-click buttons, when mobile browsers retry failed requests, or when front-end code doesn't disable the button during the API call.

## Why Stripe's API Doesn't Protect You

Stripe has idempotency keys, but they only help if you're sending the same request twice with the same key. A double-click from the browser generates two separate requests — often with different timestamps in the payload — so Stripe treats them as two distinct operations.

The fix has to happen in your application layer before the Stripe API call.

## The Redis Lock Pattern

```php
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ConcurrentOperationException;

public function subscribe(User $user, string $priceId, int $quantity = 1): void
{
    $lockKey = "billing.mutation.{$user->id}";
    $lock = Cache::lock($lockKey, 35); // 35-second timeout

    if (! $lock->get()) {
        throw new ConcurrentOperationException(
            'Another billing operation is in progress. Please wait and try again.'
        );
    }

    try {
        // Eager load required by Cashier
        $user->load('subscriptions.items');

        DB::transaction(function () use ($user, $priceId, $quantity) {
            $user->newSubscription('default', $priceId)
                ->quantity($quantity)
                ->create($user->defaultPaymentMethod()?->id);
        });
    } finally {
        $lock->release();
    }
}
```

The key design decisions:

**Per-user lock key** — `billing.mutation.{$user->id}` means only one billing operation per user at a time, not a global lock that would block all users.

**35-second timeout** — Stripe API calls typically complete in 1–3 seconds. 35 seconds gives plenty of room for slow Stripe responses while still self-releasing if the process dies.

**`finally` block** — Always release the lock, even if the Stripe call throws. Without this, a failed subscription attempt would block the user for 35 seconds.

**DB transaction** — The lock prevents concurrent Stripe calls. The transaction ensures your database state and the Stripe state stay in sync.

## Handling the Lock Failure

When the lock fails to acquire, you throw `ConcurrentOperationException`. In the controller:

```php
try {
    $this->billingService->subscribe($user, $priceId);
    return back()->with('success', 'Subscription created.');
} catch (ConcurrentOperationException $e) {
    return back()->withErrors(['billing' => $e->getMessage()]);
} catch (IncompletePayment $e) {
    return redirect()->route('cashier.payment', $e->payment->id);
}
```

The user sees a clear error: "Another billing operation is in progress. Please wait and try again." This is better than a cryptic 500 error or — worse — a silent double-charge.

## The Eager Loading Requirement

One gotcha: Cashier's `cancel()`, `swap()`, and `resume()` methods internally access `$subscription->owner` and `$subscription->items->subscription`. Without eager loading, each call triggers additional queries.

Worse, if the subscription was loaded without the relationship, Cashier throws:
```
Attempt to read property "stripe_id" on null
```

Always eager load before calling Cashier methods:

```php
$subscription = $user->subscriptions()
    ->with(['owner', 'items.subscription'])
    ->where('stripe_status', 'active')
    ->first();
```

## Testing the Lock Behavior

```php
it('rejects concurrent subscription attempts', function () {
    $user = User::factory()->create();

    // Simulate lock already held
    Cache::lock("billing.mutation.{$user->id}", 35)->get();

    expect(fn () => app(BillingService::class)->subscribe($user, 'price_123'))
        ->toThrow(ConcurrentOperationException::class);
});
```

This pattern ships in Laravel React Starter's `BillingService`. All subscription mutations — subscribe, cancel, resume, swap, updateQuantity — acquire the lock before touching Stripe.

## Summary

- Use `Cache::lock()` with a per-user key before any Stripe subscription mutation
- Always release in a `finally` block
- Wrap the Stripe call in a DB transaction
- Eager load `subscriptions.items` before Cashier method calls
- Test the lock failure path explicitly — it's the path that prevents double-charges
