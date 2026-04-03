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

## Full Audit Report
- JSON: AUDIT_REPORT_2026-04-03.json (51 findings, 17 areas)
- Markdown: AUDIT_REPORT_2026-04-03.md
- Prior batch: saas-audit-results_dfb85618-a29e-4541-b6db-79e85fc31521.json
