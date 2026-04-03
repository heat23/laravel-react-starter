# Sprint 2 — Security & Compliance Fixes
# Each section is a standalone /v prompt for a single finding.

---

## Fix LEGAL-002: Persist marketing consent server-side with consent ID

**Finding:** `ConsentController::store()` only persists `analytics_consent` to UserSetting. Marketing consent is only logged. GDPR requires both consent decisions to be independently auditable per authenticated user.

**File:** `app/Http/Controllers/Api/ConsentController.php`

**Changes:**
1. Generate a UUID for each consent decision
2. Persist marketing consent to UserSetting using key 'marketing_consent'
3. Include consent_id in the Log::info call
4. Return consent_id in the API response

```php
$consentId = (string) Str::uuid();

if ($request->user()) {
    $request->user()->setSetting(AuditService::ANALYTICS_CONSENT_KEY, (bool) ($validated['categories']['analytics'] ?? false));
    $request->user()->setSetting('marketing_consent', (bool) ($validated['categories']['marketing'] ?? false));
}

Log::info('cookie_consent_recorded', [
    'consent_id' => $consentId,
    'user_id' => $request->user()?->id,
    'categories' => $validated['categories'],
    'ip' => $request->ip(),
]);

return response()->json(['success' => true, 'consent_id' => $consentId]);
```

**Also:** Verify `PersonalDataExportController` exports both `analytics_consent` and `marketing_consent` from user settings.

**Acceptance criteria:**
- `php artisan test --filter ConsentController` passes
- API response includes `consent_id` UUID
- Authenticated user has both analytics_consent and marketing_consent in user_settings table after consent

---

## Fix LEGAL-003: Tiered audit log retention for security events

**Finding:** Single 90-day retention period for all events. Auth events should be retained 365 days.

**File:** `app/Console/Commands/PruneAuditLogs.php` + `config/health.php`

**Changes:**

Add to `config/health.php`:
```php
'audit_retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 90),
'audit_security_retention_days' => (int) env('AUDIT_LOG_SECURITY_RETENTION_DAYS', 365),
```

Add to `.env.example`:
```
AUDIT_LOG_RETENTION_DAYS=90
AUDIT_LOG_SECURITY_RETENTION_DAYS=365
```

Update `PruneAuditLogs::handle()`:
```php
$securityPrefixes = ['auth.', 'admin.unauthorized_access', 'admin.impersonation'];
$securityRetention = config('health.audit_security_retention_days', 365);
$generalRetention = config('health.audit_retention_days', 90);

// Delete general events older than general retention
AuditLog::where('created_at', '<', now()->subDays($generalRetention))
    ->where(function ($q) use ($securityPrefixes) {
        foreach ($securityPrefixes as $prefix) {
            $q->where('event', 'not like', $prefix . '%');
        }
    })
    ->delete();

// Delete security events older than security retention
AuditLog::where('created_at', '<', now()->subDays($securityRetention))
    ->where(function ($q) use ($securityPrefixes) {
        foreach ($securityPrefixes as $prefix) {
            $q->orWhere('event', 'like', $prefix . '%');
        }
    })
    ->delete();
```

**Acceptance criteria:**
- `php artisan test --filter PruneAuditLogs` passes with both retention periods tested
- Auth events not pruned before 365 days in tests

---

## Fix LEGAL-004: Remove unsubscribe link from transactional billing notifications

**Finding:** `PaymentFailedNotification`, `IncompletePaymentReminder`, `PaymentRecoveredNotification`, `RefundProcessedNotification` include `HasUnsubscribeLink`. These are required service communications—users should not be able to unsubscribe from them.

**Files:**
- `app/Notifications/PaymentFailedNotification.php`
- `app/Notifications/IncompletePaymentReminder.php`
- `app/Notifications/PaymentRecoveredNotification.php`
- `app/Notifications/RefundProcessedNotification.php`

**Change for each:** Remove `use HasUnsubscribeLink;` trait and remove the `unsubscribeLine()` call in `toMail()`.

Also update `SendDunningReminders.php` — dunning emails ARE marketing-adjacent and correctly check `marketing_emails` setting. Leave that guard in place.

**Acceptance criteria:**
- `php artisan test --filter PaymentFailedNotification` passes
- `php artisan test --filter PaymentRecovered` passes
- Billing notifications mail content does not include unsubscribe link

---

## Fix DEPLOY-001: Run PHPStan + Pint on all pushes, not just PRs

**Finding:** `.github/workflows/ci.yml` has `if: github.event_name == 'pull_request'` on the `code-quality` job. Direct pushes to main bypass PHPStan and Pint.

**File:** `.github/workflows/ci.yml`

**Change:** Remove the `if:` condition line from the `code-quality` job:
```yaml
code-quality:
  name: Code Quality
  runs-on: ubuntu-latest
  # Remove this line: if: github.event_name == 'pull_request'
  needs: [php-tests]
```

**Acceptance criteria:**
- Push to main branch triggers the code-quality job in GitHub Actions

---

## Fix DEPLOY-002: Sentry production warning + .env.example defaults

**Finding:** SENTRY_LARAVEL_DSN is commented out in .env.example. No boot-time warning when Sentry is unconfigured in production.

**Changes:**

1. In `.env.example`, uncomment and document Sentry vars:
```
# Error Monitoring (required for production)
SENTRY_LARAVEL_DSN=https://your-key@sentry.io/project-id
VITE_SENTRY_DSN=${SENTRY_LARAVEL_DSN}
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.0
SENTRY_ENVIRONMENT=${APP_ENV}
```

2. In `AppServiceProvider::boot()`, add after existing checks:
```php
if (app()->isProduction() && empty(config('sentry.dsn'))) {
    Log::warning('PRODUCTION WARNING: SENTRY_LARAVEL_DSN is not configured. Exceptions will not be tracked proactively.');
}
```

**Acceptance criteria:**
- `php artisan config:show sentry.dsn` shows the configured DSN after setting env var
- AppServiceProvider logs warning in production when DSN is empty

---

## Fix ADMIN-001: Gate audit log export to super_admin

**Finding:** Audit log CSV export is available to all admins. Full metadata including IP addresses should only be accessible to super_admin.

**File:** `routes/admin.php`

**Change:** Add `super_admin` middleware to the audit log export route:
```php
Route::get('/audit-logs/export', [AdminAuditLogController::class, 'export'])
    ->middleware('super_admin')
    ->name('admin.audit-logs.export');
```

Ensure the `super_admin` middleware is registered in `bootstrap/app.php` and the `EnsureIsAdmin` middleware (or wherever admin middleware is defined) distinguishes between `admin` and `super_admin`.

**Acceptance criteria:**
- `php artisan test --filter AdminAuditLogExport` passes
- Regular admin user (is_admin=true, not super_admin) receives 403 on GET /admin/audit-logs/export
- Super admin user receives 200 and CSV download

---

## Fix LAUNCH-002: Add maintenance mode around migrations in vps-setup.sh

**Finding:** `scripts/vps-setup.sh` runs `php artisan migrate --force` on the live database without maintenance mode. Users see errors during migration window.

**File:** `scripts/vps-setup.sh`

**Change:** Wrap the migration step:
```bash
# Before migrate
log_info "Enabling maintenance mode..."
php artisan down --render="errors::503" --secret="${DEPLOY_SECRET:-}" || true

# Existing migrate command
log_info "Running migrations..."
php artisan migrate --force

# After migrate
log_info "Bringing application back online..."
php artisan up
```

Add to `.env.example`:
```
# Deployment
DEPLOY_SECRET=                    # Set to allow bypassing maintenance mode with ?secret=value
```

**Acceptance criteria:**
- Application returns 503 for unauthenticated requests during migration step
- `php artisan up` brings app back online after migration completes
