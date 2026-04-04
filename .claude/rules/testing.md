---
description: Testing conventions — Pest, Vitest, quality rules, boot-time route limitation
globs:
  - tests/**
  - phpunit.xml
---

# Testing Conventions

**Backend:** Pest (not PHPUnit) — use `it()` / `test()` syntax. Parallel: `php artisan test --parallel`. Database: SQLite in-memory for tests.

**Frontend:** Vitest + @testing-library/react (`npm test`). All auth pages have `.test.tsx` counterparts.

**E2E:** Playwright (`tests/e2e/`) — auth smoke tests.

**Test quality rules (IMPORTANT):**
- Assert user-visible behavior, not implementation details — check redirect destinations, session flash content, and final DB state, not just that a mock was called
- Every test comment must be accurate — if a comment says "route doesn't have X", verify it. Wrong comments hide bugs.
- Inertia router calls (`router.patch`, `router.post`) are fire-and-forget — when testing hooks/components that wrap them, mock with `onSuccess` callback invocation to simulate real async behavior
- For every mutation test, verify both the success path AND the final state (e.g., `$user->fresh()->is_admin` after toggle)
- Edge case coverage required: soft-deleted users, unverified users, null/missing relationships, concurrent operations

**Boot-time route registration limitation:**
- Routes conditionally registered at boot time (e.g., `if (config('features.billing.enabled'))` in route files) cannot be tested for both enabled/disabled states in the same test suite
- Feature flags set in `phpunit.xml` determine which routes are registered at application boot
- Tests can verify route behavior when enabled (route exists) OR when disabled (route returns 404), but not both
- Workaround: Test route-specific logic (controllers, middleware) in unit tests; only test route registration in integration tests matching the phpunit.xml config
