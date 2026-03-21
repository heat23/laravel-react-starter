Model: haiku

# Pre-Flight Quality Gates Report

Session ID: `79c9e753-ea71-427f-b058-dad64cb109cd`

---

## Summary

**Overall Status: FAIL**

- 3 gates PASS
- 2 gates FAIL
- 1 gate PASS (warnings only)

Multiple test failures block merge. See details below.

---

## Gate Results

### PHP Tests (Pest)
**Status: FAIL** ❌

Command: `php artisan test --parallel --processes=4`

**Summary:**
- Passed: 1395 tests
- Failed: 2 tests
- Skipped: 6 tests
- Risky: 2 tests
- Duration: 18.06s

**Failures:**

1. **TrialNudgeNotificationTest** (tests/Unit/Notifications/TrialNudgeNotificationTest.php:24)
   - Expected subject to contain "days left"
   - Actual: "{{APP_NAME}}: Your trial ends in 4 days"

2. **WelcomeSequenceNotificationTest** (tests/Unit/Notifications/WelcomeSequenceNotificationTest.php:53)
   - Expected subject to contain "What you can do with"
   - Actual: "{{APP_NAME}}: Features you might not have found yet"

---

### JavaScript Tests (Vitest)
**Status: FAIL** ❌

Command: `npx vitest run`

**Summary:**
- Test Files: 1 failed, 80 passed
- Tests: 6 failed, 1469 passed
- Duration: 9.80s

**Failures:**
- Onboarding.test.tsx (6 test failures)
  - Navigation step assertions failing: "set your preferences" text not found
  - Likely root cause: UI text/heading changes in Onboarding component don't match test expectations

---

### Build (Vite)
**Status: PASS** ✓

Command: `npm run build`

**Summary:**
- 221 modules transformed
- Build time: 535ms
- Output: bootstrap/ssr/ssr-manifest.json + bootstrap/ssr/ssr.js
- Note: 20+ warnings about dynamic/static import conflicts (non-blocking, pre-existing)

---

### ESLint
**Status: PASS (warnings only)** ⚠️

Command: `npm run lint`

**Summary:**
- Errors: 0
- Warnings: 99 (all fixable)
- Common issues: import ordering, fast-refresh-only-exports

**Severity:** Low — warnings do not block, many fixable with `--fix`

---

### TypeScript
**Status: PASS** ✓

Command: `npx tsc --noEmit`

**Summary:**
- No type errors detected
- tsconfig.json validated

---

### PHPStan
**Status: PASS** ✓

Command: `./vendor/bin/phpstan analyse --memory-limit=1G`

**Summary:**
- Processed: 207 files
- No errors detected
- Result: OK

---

### Pint (Code Formatting)
**Status: PASS** ✓

Command: `./vendor/bin/pint --test`

**Summary:**
- Result: pass
- No formatting violations

---

### Security Audits
**Status: PASS** ✓

Commands:
- `composer audit` → No security vulnerability advisories found
- `npm audit --audit-level=critical` → Found 0 vulnerabilities

---

## Blocking Issues

1. **PHP Test Failures (2 tests)**
   - TrialNudgeNotificationTest: Subject text mismatch
   - WelcomeSequenceNotificationTest: Subject text mismatch
   - Root cause: Notification subject templates changed but tests not updated

2. **JavaScript Test Failures (6 tests)**
   - Onboarding.test.tsx: Multiple navigation step assertions failing
   - Root cause: UI text/structure changes in Onboarding component

---

## Recommended Actions

1. Fix notification test expectations in:
   - `/tests/Unit/Notifications/TrialNudgeNotificationTest.php`
   - `/tests/Unit/Notifications/WelcomeSequenceNotificationTest.php`

2. Fix Onboarding component tests:
   - `/resources/js/Pages/Onboarding.test.tsx`
   - Verify step text matches rendered UI output

3. Run `npm run lint -- --fix` to auto-fix import ordering warnings (optional, non-blocking)

---

## Gate Execution Context

- Project root: `/Users/sood/dev/heatware/laravel-react-starter`
- Worktree: (none — running in main project)
- Pre-existing dirty files: 61 (from prior sessions, ignored)
- Parallel processes: 4 (PHP tests)

