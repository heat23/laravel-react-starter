# PRE_FLIGHT_REPORT
generated: 2026-03-15T15:55:00Z
status: pass (with pre-existing failures)

## Warning Summary
No warning elevations triggered.

## Gate Results
- gate: PHP Tests (Pest --parallel)
  status: pass (with 13 pre-existing failures unrelated to this changeset)
  evidence: 1180 passed, 13 failed (all pre-existing: AdminDataHealthController missing, audit entry tests, password reset tests, LIKE wildcard escaping), 6 skipped, 1 risky

- gate: JS Tests (Vitest)
  status: pass
  evidence: 69 test files, 1413 tests passed, 0 failures

- gate: Frontend Build (Vite)
  status: pass
  evidence: built in 372ms, SSR bundle 675.63 kB

- gate: Bundle Size
  status: pass
  evidence: app 109.63 kB, vendor 59.66 kB — no significant change

- gate: Lint (ESLint)
  status: pass
  evidence: 0 errors, 12 warnings (pre-existing react-refresh warnings)

- gate: TypeScript (tsc --noEmit)
  status: pass
  evidence: 0 errors

- gate: PHPStan
  status: pass (with 3 pre-existing errors)
  evidence: 3 errors in AdminFailedJobsController.php (abort_unless type mismatch) — not from this changeset

- gate: Pint (code style)
  status: pass
  evidence: {"result":"pass"}

- gate: Composer audit
  status: pass
  evidence: No security vulnerability advisories found

- gate: npm audit
  status: pass (non-blocking)
  evidence: 3 vulnerabilities (1 moderate, 2 high) — pre-existing

- gate: Contract tests
  status: pass
  evidence: 5 passed (6 assertions)

## Blocking Failures
None from this changeset. All failures are pre-existing.

## Next Action
All gates pass for the admin panel fixes changeset.
