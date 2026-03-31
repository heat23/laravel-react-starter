# Audit Fix Prompts

Generated from audit `d01038f9-3418-4c74-a6c0-6b0ae1ac4918` on 2026-03-31.

Each file contains a `/v` prompt to fix a specific audit finding. Run them in priority order.

## Immediate (before next deploy)

| # | File | Finding | Severity |
|---|------|---------|----------|
| 1 | [SEC-005](SEC-005-fix-audit-log-route-ordering.md) | Fix audit-logs route ordering bug | HIGH |
| 2 | [SEC-001](SEC-001-add-retention-coupon-form-request.md) | Add Form Request to retention coupon | CRITICAL |
| 3 | [DATA-004/005](DATA-004-005-fix-retention-coupon-cache-and-enum.md) | Fix cache invalidation + audit enum | MEDIUM |

## Short-term (this sprint)

| # | File | Finding | Severity |
|---|------|---------|----------|
| 4 | [SEC-002/003](SEC-002-003-feature-gate-api-routes.md) | Feature-gate notification + webhook API routes | HIGH |
| 5 | [SEC-004](SEC-004-add-resume-form-request.md) | Add Form Request to resume endpoint | HIGH |
| 6 | [SEC-006](SEC-006-fix-duplicate-subscription-query.md) | Fix duplicate subscription query | HIGH |
| 7 | [DATA-001](DATA-001-add-transaction-to-toggleActive.md) | Add transaction to toggleActive | MEDIUM |
| 8 | [AUTH-002](AUTH-002-add-password-confirmation-billing.md) | Password confirmation on billing mutations | MEDIUM |

## Medium-term (this quarter)

| # | File | Finding | Severity |
|---|------|---------|----------|
| 9 | [PERF-002](PERF-002-cache-subscription-status.md) | Cache subscription status per-user | MEDIUM |
