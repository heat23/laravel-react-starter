/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

## Findings to Fix

### Fix 1: LAUNCH-011 — Debug mode detection only in admin panel (P1, 0.5h est.)
**Problem:** APP_DEBUG=true detection exists only in AdminConfigController. Should be in HealthCheckService and deploy verification.
**Files:** app/Services/HealthCheckService.php, scripts/vps-verify.sh
**Test first:** tests/Unit/Services/HealthCheckServiceTest.php — add test: `it('warns when APP_DEBUG is true in production')`.
**Implementation:** Add a debug mode check to HealthCheckService that returns a warning when APP_DEBUG=true AND APP_ENV=production. Also add a check in scripts/vps-verify.sh.
**Verify:** `php artisan test --filter=HealthCheckServiceTest`

### Fix 2: LAUNCH-001 — CSP defaults to report-only (P1, 0.25h est.)
**Problem:** config/security.php CSP_REPORT_ONLY defaults to true. Production should enforce CSP.
**Files:** .env.example, config/security.php
**Test first:** N/A (config change)
**Implementation:** Add `CSP_REPORT_ONLY=false` to the production settings comment block in .env.example alongside the other production toggles. Consider changing default to `env('CSP_REPORT_ONLY', app()->isProduction() ? false : true)`.
**Verify:** Manual check that CSP header is Content-Security-Policy (not Report-Only) when CSP_REPORT_ONLY=false.

### Fix 3: LAUNCH-005 — Static robots.txt shadows dynamic route (P3, 0.1h est.)
**Problem:** public/robots.txt (static) and SeoController::robots (dynamic) both exist. Static file wins in production. Dynamic route is smarter (env-aware, includes sitemap, blocks auth pages).
**Files:** public/robots.txt (delete), app/Http/Controllers/SeoController.php
**Test first:** tests/Feature/SeoTest.php should already test the dynamic route.
**Implementation:** Delete public/robots.txt so the dynamic SeoController route takes effect.
**Verify:** `php artisan test --filter=SeoTest`

### Fix 4: LAUNCH-006 — Missing og:image for social sharing (P2, 1h est.)
**Problem:** OpenGraph tags exist but no og:image. Social shares show blank previews.
**Files:** resources/views/app.blade.php (lines 10-14)
**Test first:** N/A (visual/meta tag)
**Implementation:** Create a default OG image (1200x630px) at public/og-image.png. Add `<meta property="og:image" content="{{ asset('og-image.png') }}" />` and `<meta property="og:image:width" content="1200" />` `<meta property="og:image:height" content="630" />` to app.blade.php. Also add `<meta name="twitter:image" content="{{ asset('og-image.png') }}" />`.
**Verify:** Test with a social media debugger tool.

### Fix 5: LAUNCH-008 — No failed job alerting (P2, 2h est.)
**Problem:** Failed jobs are visible in admin panel but no proactive alerting.
**Files:** New: app/Console/Commands/CheckFailedJobs.php, routes/console.php
**Test first:** tests/Feature/Commands/CheckFailedJobsTest.php — test that command sends notification when failed jobs > threshold.
**Implementation:** Create an Artisan command `jobs:check-failed` that queries failed_jobs table. If count > 0, send a notification to the admin email (or log a critical message for Sentry to pick up). Schedule in routes/console.php every 15 minutes.
**Verify:** `php artisan test --filter=CheckFailedJobs`

### Fix 6: LAUNCH-009 — No Dependabot configured (P3, 0.25h est.)
**Problem:** No automated dependency update tooling.
**Files:** New: .github/dependabot.yml
**Test first:** N/A (config file)
**Implementation:** Create .github/dependabot.yml with weekly schedule for composer and npm ecosystems:
```yaml
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 5
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 5
```
**Verify:** Commit and verify Dependabot creates PRs within a week.

### Fix 7: LAUNCH-002 — LOG_LEVEL guidance for production (P2, 0.1h est.)
**Problem:** .env.example has LOG_LEVEL=debug with no production guidance.
**Files:** .env.example
**Implementation:** Add comment next to LOG_LEVEL: `# Production: use 'warning' or 'info' to avoid excessive log volume and potential data exposure`
**Verify:** Visual check.

### Fix 8: OPS-001 — No circuit breaker on webhook delivery (P2, 4h est.)
**Problem:** DispatchWebhookJob retries 3x with backoff but no circuit breaker. Persistently failing endpoints saturate the queue.
**Files:** app/Jobs/DispatchWebhookJob.php, app/Models/WebhookEndpoint.php
**Test first:** tests/Feature/Webhook/WebhookCircuitBreakerTest.php — test that after N consecutive failures, endpoint is auto-disabled.
**Implementation:**
1. Add nullable `consecutive_failures` (int, default 0) and `disabled_at` (timestamp, nullable) columns to webhook_endpoints migration.
2. In DispatchWebhookJob, increment consecutive_failures on failure. If >= 5, set disabled_at and log.
3. Reset consecutive_failures to 0 on success.
4. In WebhookService, skip dispatch if endpoint has disabled_at set.
5. Add admin UI to re-enable disabled endpoints.
**Verify:** `php artisan test --filter=WebhookCircuitBreaker`

## After All Fixes

```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "fix(ops): debug detection, CSP enforcement, og:image, job alerting, dependabot, webhook circuit breaker"`
