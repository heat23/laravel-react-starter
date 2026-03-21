# Agent Review — CI/CD & Deploy Safety Fixes (2026-03-20)

## Summary

Fixes applied for audit findings DEPLOY-001, LAUNCH-001, LAUNCH-005, LAUNCH-006, LAUNCH-007.
One finding (DEPLOY-001: PHPStan on push to main) was **blocked** by the CI/CD tamper guard hook and could not be applied automatically.

## Findings Addressed

### LAUNCH-001 — APP_DEBUG guard in deploy.sh
**Status: FIXED**
Added hard `fail()` when `APP_DEBUG != false` in production. Validated against existing `fail`/`warn`/`pass` pattern in deploy.sh.

### LAUNCH-005 — Dependabot configuration
**Status: FIXED**
Created `.github/dependabot.yml` with weekly schedules for composer, npm, and github-actions. Groups defined for laravel-framework, react-ecosystem, and dev-dependencies.

### LAUNCH-006 — HSTS preload directive
**Status: FIXED (gated)**
Added `HSTS_PRELOAD` config flag in `config/security.php` (default: `false`, env: `HSTS_PRELOAD`). `SecurityHeaders` middleware appends `; preload` only when the flag is enabled. This avoids accidentally enrolling all subdomains in browser preload lists.

### LAUNCH-007 — SESSION_ENCRYPT in .env.example and deploy.sh
**Status: FIXED**
- `.env.example`: moved inline comment to its own line (dotenv parsers don't support inline comments); added commented `SESSION_SECURE_COOKIE=true` line.
- `deploy.sh`: added hard `fail()` when `SESSION_ENCRYPT != true` in production (escalated from `warn` per reviewer recommendation).

## Blocked

### DEPLOY-001 — PHPStan on push to main
**Status: BLOCKED — CI tamper guard**
The hook at `~/.claude/hooks/cicd-tamper-guard.sh` prohibits modifications to `.github/workflows/`. Manual change required:

```yaml
# In .github/workflows/ci.yml, line ~230, change:
if: github.event_name == 'pull_request'
# to:
if: github.event_name == 'pull_request' || github.ref == 'refs/heads/main'
```

## Verdict: APPROVE (with DEPLOY-001 manual follow-up required)

All auto-applicable fixes are clean. Reviewer-raised criticals (HSTS irreversibility, SESSION_ENCRYPT as fail not warn, dotenv inline comments) have been resolved.
