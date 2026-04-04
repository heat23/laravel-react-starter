# Sprint 2: Security & Compliance Hardening

**Source:** `AUDIT_FULL_REPORT_76c2377f-e028-444b-9943-bc2712a067f3.md`
**Priority:** P1 — Pre-Production
**Estimated Effort:** 2-3 days

---

## Task 1: Time-Window Limits on Admin Dashboard Queries (SEC-CRIT-003)

```
/v fix SEC-CRIT-003: In app/Http/Controllers/Admin/AdminWebhooksController.php, multiple methods perform COUNT(*) on entire tables with no timeframe filter (lines 55, 88, 168). Fix:
1. Add ->where('created_at', '>=', now()->subDays(30)) to all stats queries
2. Cache stats results using AdminCacheKey enum with 5-min TTL
3. For IncomingWebhook::distinct()->pluck('provider') (line 196), cache for 1 hour since providers change rarely
4. Review AdminBillingController and AdminDashboardController for similar unbounded queries
Write Pest tests verifying queries are bounded and caching works correctly.
```

## Task 2: Increase Lock Timeout + Stripe Idempotency Keys (SEC-HIGH-001)

```
/v fix SEC-HIGH-001: In app/Services/BillingService.php:
1. Change default lock timeout from 35s to 120s (line 20-23). Make configurable via BILLING_LOCK_TIMEOUT env var.
2. Add Stripe idempotency keys to ALL Stripe API calls. Use format: "{user_id}_{operation}_{timestamp}" passed via Cashier's ->idempotencyKey() method or direct Stripe API options.
3. Log all lock timeout events at alert level with user_id, operation, and duration.
4. Add config/billing.php if it doesn't exist with lock_timeout, stripe_idempotency_ttl settings.
Write Pest tests for: lock timeout behavior, idempotency key generation, concurrent operation rejection.
```

## Task 3: GDPR Audit Log Deletion Mechanism (SEC-HIGH-002)

```
/v fix SEC-HIGH-002: Implement GDPR-compliant audit log handling:
1. Add anonymizeForUser(int $userId) method to AuditService that replaces IP, user_agent, and identifiable metadata with '[anonymized]' for a given user
2. Call this method from wherever user account deletion is handled
3. Schedule the existing PruneAuditLogs command to run daily in routes/console.php: Schedule::command('audit:prune --days=365')->daily()
4. Add --anonymize-after=90 option to PruneAuditLogs that anonymizes (but retains) logs older than N days
Write Pest tests verifying: anonymization replaces PII fields, pruning respects retention period, user deletion triggers anonymization.
```

## Task 4: Transaction Wrapper on Trial Start (SEC-HIGH-008)

```
/v fix SEC-HIGH-008: In app/Services/PlanLimitService.php lines 31-56, startTrial() performs atomic UPDATE but isn't wrapped in DB::transaction(). Wrap the entire operation (update + audit log) in a transaction so audit failure rolls back the trial activation. Write Pest test verifying: successful trial start logs event, failed audit log rolls back trial_ends_at.
```

## Task 5: Make CI Checks Blocking (DEVOPS-HIGH-009)

```
/v fix DEVOPS-HIGH-009: In .github/workflows/ci.yml:
1. Remove continue-on-error: true from JS test coverage step (line ~154)
2. Remove continue-on-error: true from npm audit --audit-level=high step (line ~299)
3. Keep continue-on-error: true on bundle size check (advisory only for now)
4. Add coverage threshold enforcement if not present (e.g., --coverage.thresholds.statements 70)
```

## Task 6: Verified Middleware + Config Validation + Session Defaults

```
/v fix multiple MEDIUM findings:
1. SEC-MED-001: In routes/web.php line 127, add 'verified' to export route middleware array
2. SEC-MED-008: In AppServiceProvider::boot(), add validation when billing is enabled: abort_if(feature_enabled('billing') && !config('plans.tiers.pro.stripe_price_monthly'), 500, 'Stripe price IDs not configured')
3. SEC-MED-018: In .env.example, uncomment and set SESSION_SECURE_COOKIE=true and SESSION_ENCRYPT=true with comments noting these should be true in production
```
