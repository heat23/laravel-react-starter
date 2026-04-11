# Billing Review — Session 400dc773-8b0c-418e-a3bf-0e60e49685fc

Date: 2026-04-10

## Changed Billing Files

- `app/Services/BillingService.php`
- `app/Models/WebhookEndpoint.php`

## Review Checklist

### Test coverage for changed billing paths
- [x] `isUpgrade()` — 5 exhaustive tests covering upgrade, downgrade/equal, null inputs, unknown tiers, empty hierarchy with warning log
- [x] `swapPlan()` → EXPANSION lifecycle transition — tested with mock: fires when upgrading, does NOT fire when downgrading
- [x] `swapPlan()` → lifecycle failure logging — test verifies `lifecycle_transition_failed` error log with correct context when transition throws
- [x] `createSubscription()` → lifecycle failure logging — PAYING stage failure logs correctly
- [x] `cancelSubscription()` → lifecycle failure logging — CHURNED stage failure logs correctly
- [x] `swapPlan()` with null subscription — correctly throws `Error` (null setRelation call), lifecycle not invoked
- All 27 BillingService unit tests pass

### Webhook handling
- `WebhookEndpoint.php` change is documentation-only (PHPDoc explaining intentional SoftDeletes exception)
- No logic changes to webhook handling

### Eager loading before billing API calls
- `swapPlan()` still calls `$subscription->setRelation('owner', $user)` + `loadMissing('items')` + per-item setRelation before `swap()` — pattern unchanged from pre-remediation
- No eager loading regressions introduced

### PHPStan
- Clean (0 errors across 275 files)

## Summary

Changes are safe to commit. Lifecycle error logging replaces silent `catch (\Throwable)` swallowing with structured `Log::error()` calls. The `isUpgrade()` method is pure tier comparison with no Stripe API calls. No new billing paths added — only observability improvements and the EXPANSION stage transition on plan upgrades (already covered by LifecycleService tests).
