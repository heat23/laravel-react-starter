# Admin Audit Implementation Prompts

**Project:** Laravel React Starter
**Audit Date:** 2026-03-15
**Depth:** Thorough
**Total Findings:** 24 (10 P1, 14 P2)
**Estimated Total Hours:** ~52h

## Session Map

| # | File | Theme | Domain(s) | Findings | Est. Hours | Can Parallel? |
|---|------|-------|-----------|----------|------------|---------------|
| 1 | 01-quick-fixes.md | Quick fixes & data correctness | QA, UX | ADM-UX-001, ADM-QA-002, ADM-QA-006, ADM-QA-001, ADM-QA-005, ADM-UX-003, ADM-QA-003, ADM-QA-007, ADM-UX-006, ADM-QA-004 | ~8h | Yes |
| 2 | 02-audit-trail-exports.md | Audit trail & data exports | OPS, PM | ADM-OPS-001, ADM-OPS-003, ADM-PM-001, ADM-PM-004, ADM-PM-005 | ~14h | Yes |
| 3 | 03-ux-consistency.md | UX & visual consistency | UX, DES | ADM-UX-002, ADM-UX-004, ADM-UX-005, ADM-DES-001 | ~8h | Yes |
| 4 | 04-operational-tooling.md | Failed jobs & operational features | PM, OPS, AI | ADM-PM-003, ADM-OPS-002, ADM-AI-002, ADM-AI-003 | ~22h | Yes (after sessions 1-3) |

## Dependencies

- Sessions 1, 2, 3 have **no dependencies** on each other — run in parallel.
- Session 4 is independent but large. Can start in parallel; `ADM-AI-002` (data integrity) may reference patterns from session 1 fixes.

## Post-Merge Quality Gates

After all sessions merge, run:

```bash
php artisan test --parallel
npm test
vendor/bin/phpstan analyse
vendor/bin/pint --test
npm run lint
npm run build
```

Or use: `bash scripts/test-quality-check.sh`
