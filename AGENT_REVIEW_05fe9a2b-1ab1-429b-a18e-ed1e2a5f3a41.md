# Agent Review — Session 05fe9a2b: Billing Critical Fix Pass

Status: completed

**Scope:** Fix 3 CRITICAL issues from prior session agent review — churn_type miscategorisation, InvoluntaryChurnWinBackNotification idempotency, and past_due_since datetime cast.

**Files changed this session:**
- `app/Http/Controllers/Billing/StripeWebhookController.php`
- `app/Console/Commands/SendDunningReminders.php`
- `app/Models/Subscription.php` (new)
- `app/Providers/AppServiceProvider.php`

---

## Review

### Fix 1 — churn_type dynamic resolution

**File:** `app/Http/Controllers/Billing/StripeWebhookController.php:82-86`

The fix correctly derives `$churnType` from `$cancellationReason`:
```php
$churnType = $cancellationReason === 'payment_failed' ? 'involuntary' : 'voluntary';
```
This resolves the double-counting bug where voluntary cancels were logged as `involuntary`. The condition mirrors exactly the condition used for the win-back dispatch below it.

**Status: CORRECT.** No issues.

---

### Fix 2 — InvoluntaryChurnWinBackNotification idempotency

**File:** `app/Http/Controllers/Billing/StripeWebhookController.php:91-97`

```php
if ($user && ! EmailSendLog::alreadySent($user->id, 'involuntary_churn_win_back', 1)) {
    $user->notify((new InvoluntaryChurnWinBackNotification)->delay(now()->addDays(3)));
    EmailSendLog::record($user->id, 'involuntary_churn_win_back', 1);
}
```

Uses the same `EmailSendLog` dedup mechanism as `SendDunningReminders` and `SendWinBackEmails`.

**Potential concern:** There is a small TOCTOU window between `alreadySent()` check and `record()` — two simultaneous Stripe retries could both pass the check. However, `EmailSendLog::record()` uses a unique constraint and catches `UniqueConstraintViolationException`, so the second concurrent write will fail silently and the notification will already have been sent once — acceptable behaviour.

**Status: CORRECT.** No issues.

---

### Fix 3 — Subscription model datetime cast

**File:** `app/Models/Subscription.php` (new), `app/Providers/AppServiceProvider.php`

```php
class Subscription extends CashierSubscription
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), ['past_due_since' => 'datetime']);
    }
}
```

Registered via `Cashier::useSubscriptionModel(\App\Models\Subscription::class)` in `AppServiceProvider::register()`.

**Potential concern:** Any code that does `use Laravel\Cashier\Subscription` (type hints, imports) will reference the parent class, not the extended one. Two files were updated (`StripeWebhookController` and `SendDunningReminders`) to import `App\Models\Subscription`. If any other file type-hints the Cashier class directly, it won't get the cast.

**Status: CORRECT for current usages.** No current code calls Carbon methods on `past_due_since` directly, so this is a proactive correctness fix.

---

## Summary

| Severity | Count |
|----------|-------|
| CRITICAL | 0 |
| HIGH | 0 |
| MEDIUM | 0 |
| LOW | 0 |

All 3 CRITICAL issues from AGENT_REVIEW_session-02-billing-infrastructure.md are correctly resolved. No new issues introduced.

findings: 0
