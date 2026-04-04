# v-prompts — Audit Fix Prompts
Generated: 2026-04-03 from AUDIT_REPORT_2026-04-03.md

Each file contains ready-to-run /v prompts. Pass the file contents to /v or copy individual sections.

## Files

| File | Sprint | Content |
|------|--------|---------|
| sprint-1-launch-blockers.md | Sprint 1 (~10h) | SEC-001, OPSRISK-001, LEGAL-001, LAUNCH-001, FUNNEL-001, COPY-001 |
| sprint-2-security-compliance.md | Sprint 2 (~12h) | LEGAL-002-004, DEPLOY-001-002, ADMIN-001, LAUNCH-002-003 |
| sprint-3-quality-gtm.md | Sprint 3 (~15h) | GTM-001-002, FUNNEL-002-003, ADMIN-002-003, SEO-001, LAUNCH-003-004 |
| sprint-4-polish.md | Sprint 4 (~18h) | All P3: A11Y, DS, UX, DOCS, SEO, ARCH, FUNNEL, ADMIN |

## P1 Quick Fixes

Fix FUNNEL-001 immediately (runtime crash):
  /v fix CheckExpiredTrials to use Carbon::parse() before calling ->toISOString()
  File: app/Console/Commands/CheckExpiredTrials.php

Fix LEGAL-001 immediately (visible disclaimer in production):
  /v fix LegalContent.tsx to only render the Template disclaimer when NODE_ENV=development

Fix OPSRISK-001 + SEC-001 (.env defaults):
  /v update .env.example to default CACHE_STORE=redis and QUEUE_CONNECTION=redis with AppServiceProvider boot warnings when billing is enabled with non-Redis drivers

## Comprehensive Audit (2026-04-04)

| File | Sprint | Content |
|------|--------|---------|
| 09-sprint-1-security-critical.md | Sprint 1 (~2d) | SEC-CRIT-001/002/004, SEC-HIGH-003/004/005/006/012 |
| 10-sprint-2-security-compliance.md | Sprint 2 (~3d) | SEC-CRIT-003, SEC-HIGH-001/002/008, DEVOPS-HIGH-009, SEC-MED-001/008/018 |
| 11-sprint-3-test-coverage.md | Sprint 3 (~5d) | TEST-CRIT-005/006, TEST-HIGH-010/011, TEST-MED-011/012 |
| 12-sprint-4-performance-polish.md | Sprint 4 (~4d) | PERF-HIGH-007, SEC-MED-009/010, UX-MED-019/020/021, DEVOPS-MED-013/014 |

## Comprehensive SaaS Audit (2026-04-04, session bac592f1)

| File | Sprint | Content |
|------|--------|---------|
| 13-sprint-1-validation-hardening.md | Sprint 1 (~1d) | SEC-001/002/004/006/007, BIL-001, DATA-001, FE-002 |
| 14-sprint-2-billing-ci-hardening.md | Sprint 2 (~2-3d) | SEC-003/008, BIL-002/003, TEST-001, INFRA-001-004 |
| 15-sprint-3-frontend-testing-polish.md | Sprint 3 (~3-4d) | FE-003-006, TEST-002-005, PERF-001/002, INFRA-005-007 |

## Full Audit Reports
- **Comprehensive (2026-04-04, bac592f1):** audit-full-results_bac592f1.json (38 findings), AUDIT_REPORT_bac592f1.md
- **Comprehensive (2026-04-04, 76c2377f):** audit-full-results_76c2377f.json (51 findings), AUDIT_FULL_REPORT_76c2377f.md
- **Prior (2026-04-03):** AUDIT_REPORT_2026-04-03.json, AUDIT_REPORT_2026-04-03.md
- **Batch audits:** audit-*-results_dfb85618.json (admin, growth, gtm, launch)
