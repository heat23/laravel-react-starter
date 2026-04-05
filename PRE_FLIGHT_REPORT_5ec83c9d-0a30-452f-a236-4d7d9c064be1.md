Model: haiku

# Pre-Flight Report

SESSION_ID: 5ec83c9d-0a30-452f-a236-4d7d9c064be1
PROJECT_ROOT: /Users/sood/dev/heatware/laravel-react-starter
Date: 2026-04-05

## Overall Status: PASS

All gates passed.

---

## Gates

### PHP Tests — PASS

**Pre-verified by caller:** 1894 passed, 3 risky, 8 skipped.

---

### JS Tests — PASS

**Command:** `npx vitest run`

**Fix applied:** `resources/js/lib/events.sync.test.ts` snapshot updated to include `LIFECYCLE_EMAIL_SENT` (present in both PHP enum and TS events file but missing from snapshot array; floor count bumped 53→54).

**Result:** 90 test files passed, 1585 tests passed. Duration: 13.58s.

---

### Build — PASS

**Command:** `npm run build`

**Result:** Built in 581ms. Pre-existing warnings about dynamic/static import overlap from `__smoke-tests__.test.tsx` — non-blocking.

---

### Lint — PASS (warnings only)

**Command:** `npm run lint`

**Result:** 0 errors, 86 warnings (import ordering + fast-refresh advisory — pre-existing, non-blocking).

---

### TypeScript — PASS

**Command:** `npx tsc --noEmit`

**Result:** No errors.

---

### PHPStan — PASS

**Command:** `vendor/bin/phpstan analyse --memory-limit=1G`

**Result:** No errors. 274 files analysed.

---

### Security Audit — PASS

**Commands:** `composer audit` + `npm audit --audit-level=critical`

**Result:** No vulnerabilities found.

---

## Summary

| Gate        | Status  | Notes                                              |
|-------------|---------|----------------------------------------------------|
| PHP Tests   | PASS    | 1894 passed, 3 risky, 8 skipped (pre-verified)    |
| JS Tests    | PASS    | 1585/1585 passed — snapshot fix applied            |
| Build       | PASS    | Warnings only (non-blocking)                       |
| Lint        | PASS    | 0 errors, 86 warnings                              |
| TypeScript  | PASS    | No errors                                          |
| PHPStan     | PASS    | No errors, 274 files                               |
| Security    | PASS    | No vulnerabilities                                 |
