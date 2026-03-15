# PRE_FLIGHT_REPORT
generated: 2026-03-15T16:00:00Z
status: pass (all failures pre-existing)

## Gate Results
- gate: PHP Tests (Pest --parallel)
  status: pass (1194 passed, 6 skipped, 1 risky)
  evidence: 1 pre-existing failure in AdminDataHealthTest (unrelated to changes)

- gate: JS Tests (Vitest)
  status: pass
  evidence: 69 test files, 1413 tests passed

- gate: Frontend Build
  status: pass
  evidence: built in 362ms, ssr.js 677.60 kB

- gate: Bundle Size
  status: pass
  evidence: main chunk 109.82 kB

- gate: Lint (ESLint)
  status: pass
  evidence: 0 errors, 16 pre-existing warnings (resources/js/ only)

- gate: TypeScript
  status: pass
  evidence: npx tsc --noEmit — no errors

- gate: PHPStan
  status: pass (pre-existing)
  evidence: 3 pre-existing errors in AdminFailedJobsController.php (unrelated to changes)

- gate: Pint
  status: pass
  evidence: {"result":"pass"}

- gate: Composer Audit
  status: pass
  evidence: No security vulnerability advisories found

- gate: npm Audit
  status: pass (non-blocking)
  evidence: 3 pre-existing vulnerabilities (1 moderate, 2 high)

## Blocking Failures
- None (all failures pre-existing and unrelated to this session's changes)

## Next Action
- Ready to commit
