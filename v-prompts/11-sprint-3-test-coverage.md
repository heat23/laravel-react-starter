# Sprint 3: Test Coverage & Quality

**Source:** `AUDIT_FULL_REPORT_76c2377f-e028-444b-9943-bc2712a067f3.md`
**Priority:** P1 — Pre-Production
**Estimated Effort:** 4-5 days

---

## Task 1: Form Request Validation Tests (TEST-CRIT-005)

```
/v fix TEST-CRIT-005: 59 of 63 Form Requests have zero test coverage. Create tests in tests/Feature/Requests/ for the highest-risk requests first:

Priority 1 (billing/payment):
- app/Http/Requests/Billing/ (all requests)
- Test: required fields, type validation, authorization rules

Priority 2 (admin):
- app/Http/Requests/Admin/ (all requests)
- Test: admin-only authorization, filter validation, pagination bounds

Priority 3 (webhooks/security):
- app/Http/Requests/Webhook/ (all requests)
- Test: URL validation, event array validation, secret format

For each request, test: valid data passes, each required field fails when missing, type constraints enforced, authorization rule works correctly. Use Pest datasets for field-level validation testing.
```

## Task 2: Background Job Tests (TEST-CRIT-006)

```
/v fix TEST-CRIT-006: Create tests for all 5 background jobs in tests/Feature/Jobs/:

1. PersistAuditLog: Test successful persistence, handles missing user gracefully, validates required fields
2. DispatchWebhookJob: Test HTTP dispatch with signature, retry on failure, timeout handling, dead-letter on exhausted retries
3. DispatchAnalyticsEvent: Test GA payload construction, handles missing config, timeout doesn't block
4. CancelOrphanedStripeSubscription: Test identifies orphans correctly, Stripe API mock, handles API errors
5. Any other jobs in app/Jobs/

Use Queue::fake() for dispatch verification and Http::fake() for external API calls. Test both success and failure paths for each job.
```

## Task 3: Console Command Tests (TEST-HIGH-010)

```
/v fix TEST-HIGH-010: 14 of 16 console commands are untested. Create tests in tests/Feature/Commands/ for:

Priority 1 (cron-triggered):
- SendDunningReminders: Test reminder sent at correct intervals, skips already-reminded, handles missing user
- webhooks:prune-stale: Test marks abandoned deliveries, respects --hours flag
- subscriptions:check-incomplete: Test identifies incomplete, sends reminders at 1h/12h

Priority 2 (maintenance):
- All remaining commands in app/Console/Commands/

For each: test success output, error handling, dry-run behavior if supported, and edge cases (empty result sets, database errors).
```

## Task 4: Untested Service Tests (TEST-HIGH-011)

```
/v fix TEST-HIGH-011: Create unit tests for 3 untested services:

1. tests/Unit/Services/LeadScoringServiceTest.php: Test scoring algorithm, boundary conditions, null inputs
2. tests/Unit/Services/LifecycleServiceTest.php: Test state transitions, invalid transitions rejected, event emission
3. tests/Unit/Services/DataHealthServiceTest.php: Test health checks, threshold validation, reporting

Mock external dependencies. Test both happy path and error conditions.
```

## Task 5: Strengthen Status-Code-Only Assertions (TEST-MED-011)

```
/v fix TEST-MED-011: 71 tests across tests/Feature/ only assert HTTP status codes without verifying state changes. Prioritize strengthening:

1. Admin tests: After toggle-admin, assert $user->fresh()->is_admin changed. After deactivate, assert deleted_at set.
2. Billing tests: After subscribe, assert subscription record exists with correct plan. After cancel, assert grace period set.
3. Auth tests: After login, assert session contains user. After register, assert user created in DB with correct attributes.

Pattern: For every assertStatus(200/302), add at least one of:
- assertDatabaseHas/assertDatabaseMissing for mutations
- assertInertia with ->where() for page renders
- assertSessionHas for redirects
```

## Task 6: MySQL Test Runner in CI (TEST-MED-012)

```
/v fix TEST-MED-012: SQLite in-memory tests can hide MySQL-specific bugs (date functions, JSON, LIKE escaping). In .github/workflows/ci.yml, the MySQL service already exists for CI tests. Verify that the PHP test job actually uses MySQL (DB_CONNECTION=mysql) and not SQLite. If it uses SQLite in CI, switch to MySQL to match production. Keep SQLite for local development speed.
```
