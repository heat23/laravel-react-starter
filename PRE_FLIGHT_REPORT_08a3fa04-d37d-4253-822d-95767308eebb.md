Model: haiku

# Pre-Flight Quality Gate Report

**Session:** 08a3fa04-d37d-4253-822d-95767308eebb
**Project:** Laravel React Starter
**Date:** 2026-03-20
**Status:** FAIL (8 JS test failures)

**Note:** This is a dirty-tree session with 60+ uncommitted files on main branch.

---

## Gate Results

### PHP Tests
**Status:** PASS
**Command:** `php artisan test --parallel --processes=4`
**Results:**
- Passed: 1380
- Skipped: 6
- Risky: 2
- Duration: 29.33s

No failures detected.

---

### JavaScript Tests
**Status:** FAIL
**Command:** `npx vitest run`
**Results:**
- Test Files: 5 failed | 76 passed (81 total)
- Tests: 8 failed | 1467 passed (1475 total)
- Duration: 20.29s

**Failing Tests:**
1. `resources/js/Pages/Settings/ApiTokens.test.tsx` - Multiple test failures related to empty state rendering
   - Root cause: Expecting "Create a token to authenticate with the API" text when API tokens list is empty, but text not found in DOM
   - Issue appears to be empty state copy/UI not rendering as expected in test context

---

### Build
**Status:** PASS
**Command:** `npm run build`
**Results:**
- Warnings: 11 Vite dynamic import optimization warnings (expected for test-accessed admin pages)
- Output: `bootstrap/ssr/ssr-manifest.json` (11.31 kB), `bootstrap/ssr/ssr.js` (1,417.68 kB)
- Duration: 509ms

---

### TypeScript Type Checking
**Status:** PASS
**Command:** `npx tsc --noEmit`
**Results:** No type errors detected

---

### Linting (ESLint)
**Status:** PASS (with warnings)
**Command:** `npm run lint`
**Results:**
- Errors: 0
- Warnings: 94 (mostly import order and react-refresh optimization tips)
- Fixable: 72 warnings

Example issues: Import ordering in `Webhooks.tsx`, `Welcome.tsx`, `app.tsx`; React Fast Refresh exports in `app.tsx`.

---

### Code Style (Pint)
**Status:** PASS
**Command:** `./vendor/bin/pint --test`
**Results:** {"result":"pass"}

---

### Static Analysis (PHPStan)
**Status:** PASS
**Command:** `./vendor/bin/phpstan analyse --memory-limit=1G`
**Results:**
- Analyzed: 207 files
- Errors: 0

---

### Security Audits
**Status:** PASS

#### Composer Audit
**Command:** `composer audit`
**Results:** No security vulnerability advisories found.

#### NPM Audit
**Command:** `npm audit --audit-level=critical`
**Results:** found 0 vulnerabilities

---

## Summary

| Gate | Status | Notes |
|------|--------|-------|
| PHP Tests | PASS | 1380 passed, 2 risky, 6 skipped |
| JS Tests | **FAIL** | 8 test failures (4 in ApiTokens, 4 other) |
| Build | PASS | Successful production build |
| TypeScript | PASS | No type errors |
| ESLint | PASS | 94 warnings (non-blocking) |
| Pint | PASS | All PHP code style compliant |
| PHPStan | PASS | 207 files analyzed, 0 errors |
| Composer Audit | PASS | No vulnerabilities |
| NPM Audit | PASS | No critical vulnerabilities |

**Overall Status:** **FAIL**

The quality gate run detected 8 failing JavaScript tests in the Vitest suite. The primary issue is in `resources/js/Pages/Settings/ApiTokens.test.tsx` where empty state UI assertions are failing. All other gates (PHP tests, build, type checking, linting, static analysis, and security audits) passed successfully.

Fix the failing JS tests before deploying this session to production.
