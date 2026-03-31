# Audit Fix Prompts

## Latest Audit: `b29447e2` (2026-03-31, Comprehensive)

Each phase file contains `/v` prompts to fix audit findings. Run them in priority order.

| File | Findings | Priority |
|------|----------|----------|
| [phase-1-before-production.md](phase-1-before-production.md) | SEC-001, SEC-002, SEC-003, PERF-001, JOBS-001, CI-001, CI-002, INFRA-008 | HIGH - Before production |
| [phase-2-before-ga.md](phase-2-before-ga.md) | AUTH-001, ARCH-001, ARCH-002, FE-002, FE-003, INFRA-001, INFRA-005, INFRA-006, INFRA-007, SEC-005 | MEDIUM - Before GA |
| [phase-3-post-ga-hardening.md](phase-3-post-ga-hardening.md) | TEST-001-003, FE-001, INFRA-002-004, ARCH-003, AUTH-002 | MEDIUM - Post-GA |
| [phase-4-polish.md](phase-4-polish.md) | All LOW-severity items | LOW - Ongoing |

### Related Artifacts

- `AUDIT_REPORT_b29447e2.json` - Machine-readable findings (55 total)
- `AUDIT_REPORT_b29447e2.md` - Human-readable report

---

## Previous Audit: `d01038f9` (2026-03-31)

### Immediate (before next deploy)

| # | File | Finding | Severity |
|---|------|---------|----------|
| 1 | [SEC-005](SEC-005-fix-audit-log-route-ordering.md) | Fix audit-logs route ordering bug | HIGH |
| 2 | [SEC-001](SEC-001-add-retention-coupon-form-request.md) | Add Form Request to retention coupon | CRITICAL |
| 3 | [DATA-004/005](DATA-004-005-fix-retention-coupon-cache-and-enum.md) | Fix cache invalidation + audit enum | MEDIUM |

### Short-term (this sprint)

| # | File | Finding | Severity |
|---|------|---------|----------|
| 4 | [SEC-002/003](SEC-002-003-feature-gate-api-routes.md) | Feature-gate notification + webhook API routes | HIGH |
| 5 | [SEC-004](SEC-004-add-resume-form-request.md) | Add Form Request to resume endpoint | HIGH |
| 6 | [SEC-006](SEC-006-fix-duplicate-subscription-query.md) | Fix duplicate subscription query | HIGH |
| 7 | [DATA-001](DATA-001-add-transaction-to-toggleActive.md) | Add transaction to toggleActive | MEDIUM |
| 8 | [AUTH-002](AUTH-002-add-password-confirmation-billing.md) | Password confirmation on billing mutations | MEDIUM |

### Medium-term (this quarter)

| # | File | Finding | Severity |
|---|------|---------|----------|
| 9 | [PERF-002](PERF-002-cache-subscription-status.md) | Cache subscription status per-user | MEDIUM |
