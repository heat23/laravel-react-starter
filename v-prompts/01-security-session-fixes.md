/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

## Findings to Fix

Work through these in order. For each one: write the test first (TDD for backend, test-after for UI), implement the fix, run the verification command, then move to the next.

### Fix 1: SEC-004 — Social auth callback lacks session regeneration (P2, 0.25h est.)
**Problem:** SocialAuthController::callback() calls Auth::login() but does not call $request->session()->regenerate(), unlike AuthenticatedSessionController. This is a session fixation risk.
**Files:** app/Http/Controllers/Auth/SocialAuthController.php (line ~88-101)
**Test first:** tests/Feature/Auth/SocialAuthTest.php — add test: `it('regenerates session after social auth login')`. Setup: create user, mock Socialite, capture session ID before callback, assert session ID changes after callback.
**Implementation:** After `Auth::login($user, remember: false)`, add `$request->session()->regenerate();` before the redirect.
**Verify:** `php artisan test --filter=SocialAuthTest`

### Fix 2: ADM-002 — Impersonation does not regenerate session (P2, 0.5h est.)
**Problem:** AdminImpersonationController::start() and stop() call Auth::login() without session regeneration. Defense-in-depth gap.
**Files:** app/Http/Controllers/Admin/AdminImpersonationController.php (lines ~44-50 for start, ~85-86 for stop)
**Test first:** tests/Feature/Admin/AdminImpersonationTest.php — add two tests: `it('regenerates session on impersonation start')` and `it('regenerates session on impersonation stop')`. Capture session ID before and after, assert they differ.
**Implementation:** In start(): After putting session data and Auth::login, call `$request->session()->regenerate()`. In stop(): After Auth::login($admin), call `$request->session()->regenerate()`. Note: session data set before regenerate() is preserved by Laravel's regenerate().
**Verify:** `php artisan test --filter=AdminImpersonationTest`

### Fix 3: ADM-001 — LIKE wildcards not escaped in admin search (P2, 0.5h est.)
**Problem:** AdminUsersController::index() and AdminBillingStatsService::getFilteredSubscriptions() interpolate search into LIKE without escaping % and _ wildcards. FeatureFlagService correctly escapes these — follow that pattern.
**Files:** app/Http/Controllers/Admin/AdminUsersController.php (line ~30-33), app/Services/AdminBillingStatsService.php (line ~150-154)
**Test first:** tests/Feature/Admin/AdminDashboardTest.php or a new test — `it('escapes LIKE wildcards in user search')`. Create users named "test%user" and "normal_user", search for "test%", assert only exact match returned.
**Implementation:** Before interpolation, escape: `$search = str_replace(['%', '_'], ['\\%', '\\_'], $search);` — same pattern as FeatureFlagService line ~303-304.
**Verify:** `php artisan test --filter=AdminDashboardTest`

### Fix 4: ADM-003 — bulkDeactivate uses inline validation (P3, 0.25h est.)
**Problem:** AdminUsersController::bulkDeactivate() uses inline $request->validate() instead of a Form Request class, inconsistent with all other admin endpoints.
**Files:** app/Http/Controllers/Admin/AdminUsersController.php (line ~136-141)
**Test first:** Existing tests in tests/Feature/Admin/AdminBulkActionsTest.php should continue passing.
**Implementation:** Create app/Http/Requests/Admin/AdminBulkDeactivateRequest.php with authorize() returning Gate::allows('admin') and rules() with the existing validation. Update controller to type-hint the new request.
**Verify:** `php artisan test --filter=AdminBulkActionsTest`

### Fix 5: SEC-002 — Inline validation in auth controllers (P3, 1h est.)
**Problem:** PasswordResetLinkController::store and NewPasswordController::store use inline $request->validate() instead of Form Requests.
**Files:** app/Http/Controllers/Auth/PasswordResetLinkController.php (line ~38), app/Http/Controllers/Auth/NewPasswordController.php (line ~37)
**Test first:** Existing tests in tests/Feature/Auth/PasswordResetTest.php should continue passing.
**Implementation:** Create PasswordResetLinkRequest and NewPasswordRequest in app/Http/Requests/Auth/. Move validation rules to those classes. Update controller type hints.
**Verify:** `php artisan test --filter=PasswordResetTest`

### Fix 6: SEC-003 — Health check query token deprecation (P3, 0.5h est.)
**Problem:** HealthCheckController still accepts tokens via query parameter (?token=...) which leaks into logs.
**Files:** app/Http/Controllers/HealthCheckController.php (line ~54-58), config/health.php
**Test first:** tests/Feature/Admin/AdminHealthTest.php — add test: `it('rejects query token when allow_query_token is false')`.
**Implementation:** Add 'allow_query_token' => env('HEALTH_ALLOW_QUERY_TOKEN', false) to config/health.php. In HealthCheckController, check this config before accepting query tokens.
**Verify:** `php artisan test --filter=AdminHealthTest`

### Fix 7: DEPLOY-001 — Tighten npm audit in CI (P3, 0.1h est.)
**Problem:** npm audit --audit-level=high uses continue-on-error: true in CI.
**Files:** .github/workflows/ci.yml (line ~299-300)
**Test first:** N/A (CI config change)
**Implementation:** Remove `continue-on-error: true` from the high-severity npm audit step.
**Verify:** Push to a branch and verify CI behavior.

## After All Fixes

Run the full verification suite:
```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "fix(security): session regeneration, LIKE escaping, Form Request consistency, CI hardening"`
