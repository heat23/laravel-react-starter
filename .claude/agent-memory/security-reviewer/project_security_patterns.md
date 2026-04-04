---
name: project_security_patterns
description: Security patterns, conventions, and known issues discovered during security audits of the laravel-react-starter codebase
type: project
---

Security infrastructure in place as of 2026-04-04 (tenth full audit pass):

- Rate limiting on all auth endpoints (login, register, password reset, 2FA challenge) in routes/auth.php
- CSRF exclusion only for `stripe/webhook` in bootstrap/app.php; incoming webhook API routes are on api middleware (no CSRF needed)
- Webhook HMAC-SHA256 via VerifyWebhookSignature middleware with hash_equals timing-safe comparison
- Replay protection for Stripe webhooks (5-min tolerance) BUT check happens AFTER signature verification (correct order)
- BillingService wraps all Stripe mutations in Redis locks (35s) + DB transactions
- StripeWebhookController refuses to boot in non-local environments without STRIPE_WEBHOOK_SECRET set
- Impersonation guard: cannot impersonate another admin, deactivated user, or self; session encrypted with Crypt::encryptString
- Stop-impersonation route intentionally lacks `verified` middleware (documented, correct)
- Admin routes protected by `auth + verified + admin + throttle:60,1`; super_admin routes layer `super_admin` middleware
- CSP enabled but defaults to report-only mode in local/testing; `style-src 'unsafe-inline'` required by Tailwind JIT
- dangerouslySetInnerHTML: QR code uses DOMPurify with SVG profile; Blog/Show uses DOMPurify allowlist; JSON-LD scripts use `</script>` escaping only (no DOMPurify — documented as intentional to avoid JSON corruption); Pricing.tsx uses sanitizeHtml() on licenseFaqSchema (DOMPurify.sanitize with no options on JSON-LD — functionally may corrupt JSON but data is hardcoded server config, not user input); FaqJsonLd component also uses </script> escaping only — all FAQ data is hardcoded static data
- .env is in .gitignore and is NOT tracked in git history; .env.example has only placeholder comments
- No hardcoded secrets found in config/ files
- BlogController::loadPost uses slug constrained by route regex `[a-z0-9-]+` which prevents path traversal via `..`
- QueryHelper::dateExpression validates column name against `[a-zA-Z_][a-zA-Z0-9_.]*` before interpolating into raw SQL — safe
- QueryHelper::whereLike uses parameterized LIKE with proper LIKE wildcard escaping — safe
- User $fillable does NOT include is_admin or super_admin (removed — finding #6 FIXED in 9th audit confirmed in 10th)
- UpdateSettingRequest restricts setting keys to an allowlist (theme, timezone, onboarding_completed, sidebar_state) — no arbitrary key injection
- AdminUsersController::update passes only name+email (from AdminUpdateUserRequest) — no privilege escalation via update
- UTM data written to UserSetting::setValue at registration — keys come from middleware whitelist (utm_source etc), not user input
- ApiTokens: TokenController uses auth:sanctum + user relationship scoping — no cross-user token access
- WebhookEndpointController: all CRUD operations scoped to $request->user()->webhookEndpoints() — IDOR protected
- composer audit: no vulnerabilities; npm audit: no vulnerabilities (as of 2026-03-22; not re-run in 10th audit)
- Session: secure+httponly in production, same_site=lax, encrypt in production — correctly configured
- IncomingWebhookController: uses $request->all() for payload storage but this is intentional — the full payload is stored in incoming_webhooks table for processing; signature is already verified by VerifyWebhookSignature middleware before this controller is reached
- BreadcrumbJsonLd component: breadcrumbs data is entirely server-controlled static data from controllers; no user input flows into the JSON-LD — confirmed safe
- AdminScheduleController: exposes schedule command strings, but these are registered at boot time from code — not user input
- Feedback model byType/byStatus scopes use parameterized WHERE not raw SQL — no injection risk even with unvalidated input
- LoginRequest has BOTH route-level throttle:10,1 AND app-level RateLimiter in ensureIsNotRateLimited (5 attempts with IP+email key) — double-layer brute force protection
- ExportController::users is scoped exclusively to the authenticated user's own record (WHERE id = request->user()->id) — no cross-user data exposure
- AdminFailedJobsController: bulkRetry passes UUIDs directly to Artisan::call('queue:retry', ['id' => [$uuid]]) — Artisan wraps these as arguments, not shell-interpolated, so no command injection
- AdminSystemController: uses Laravel Process facade (not shell_exec/exec), and runs 'node --version' which is a hardcoded string — no injection risk; restricted to super_admin (fixed in 9th audit)
- ProductAnalyticsService: $nameExpr (JSON_EXTRACT expression) is hardcoded PHP string based on DB driver, not user input — safe
- CustomerHealthService: whereRaw expression is hardcoded DB-driver-conditional expression — not user input, safe
- LifecycleService::getStageVelocity: raw DB::select() with string-interpolated $diffExpr — expression is hardcoded PHP logic based on DB driver, not user input; safe
- UnsubscribeController: uses signed URL (hasValidSignature) for authentication of unauthenticated route — correct security pattern; throttled at 10/min
- NpsSurveyController: uses inline $request->validate() instead of a Form Request — validation is present and correct (score 0-10, comment nullable/max:500, survey_trigger allowlist) so no security gap, just a style inconsistency
- ConsentController: uses inline $request->validate() with proper regex constraints on version and timestamp — safe
- New email sequence commands (SendWelcomeSequence, SendReEngagementEmails, SendWinBackEmails, SendDunningReminders, SendOnboardingReminders, SendTrialEndingReminders, SendTrialNudges): all run as console commands only, no user-facing routes, no user-controlled input into queries
- SendWinBackEmails::alreadySentEmail uses a raw LIKE pattern against the notifications JSON column ("%\"email_number\":N[^0-9]%") — $emailNumber is an int from the hardcoded EMAIL_SCHEDULE constant, not user input; no injection risk
- LeadScoringService: all DB queries use parameterized bindings (DB::table()->where()), no raw SQL with user input
- EngagementScoringService: all DB queries are parameterized; scoreBatch/score methods receive User objects from command chunks, not user-controlled data
- CustomerHealthService: primeLoginCountCache/primeBillingCache use whereIn/pluck with integer user IDs from DB — no injection risk
- CRM lifecycle commands (QualifyLeads, ComputeUserScores, CheckExpiredTrials) run server-side as scheduled jobs, no user-controlled inputs
- AdminTokensController::revoke now relies entirely on route middleware (super_admin) — no inline abort_unless (FIXED in 10th audit)
- AdminWebhooksController::restoreEndpoint now relies entirely on route middleware (super_admin) — no inline abort_unless (FIXED in 10th audit)
- SwapPlanRequest coupon now has regex:/^[a-zA-Z0-9_-]+$/ (FIXED — confirmed in 10th audit)
- AdminBulkFeedbackRequest ids now has max:100 cap (FIXED in 10th audit)
- AdminBulkContactSubmissionRequest ids now has max:100 cap (FIXED in 10th audit)
- AdminCreateUserRequest gates is_admin rule behind isSuperAdmin() check (FIXED in 9th audit — confirmed in 10th)

Known findings from audits — STILL OPEN as of 10th audit (2026-04-04):

1. MEDIUM: UpdatePaymentMethodRequest accepts any string for payment_method with no format constraint — should add regex:/^pm_[a-zA-Z0-9]+$/ to enforce Stripe PM ID format [UNADDRESSED since 3rd audit]
2. MEDIUM: DispatchWebhookJob sends HTTP requests to user-controlled URLs with no SSRF protection — Laravel's `url:https,http` validator accepts private/internal IPs (127.0.0.1, 10.x.x.x, 169.254.169.254 etc). A user can register a webhook endpoint pointing to internal infrastructure. [UNADDRESSED since 4th audit]
3. MEDIUM: AdminUsersController::bulkDeactivate reads $request->input('ids') (line 225) not $request->validated('ids'), bypassing the max:100 cap in AdminBulkDeactivateRequest. Same bug in bulkRestore (line 254). An admin could pass an unbounded array. [UNADDRESSED since 9th audit]
4. Note: Replay protection for non-Stripe webhooks (GitHub etc) is absent — $timestamp is null for all non-Stripe providers in VerifyWebhookSignature::extractTimestamp(), so the replay tolerance check is silently skipped. Logged as a design gap since 3rd audit.

Previously fixed (verified in 10th audit):
- Finding #2 (SwapPlanRequest coupon no regex) — FIXED
- Finding #4 (AdminSystemController visible to all admins) — FIXED (9th audit)
- Finding #6 (is_admin in $fillable) — FIXED (confirmed in 10th audit)
- Finding #7/19 (AdminFeedbackController::index plain Request) — FIXED (9th audit)
- Finding #9 (inline abort_unless in tokens/webhooks controllers) — FIXED (confirmed in 10th audit)
- Finding #22 (any admin can create admin user) — FIXED (9th audit)
- Finding #55 (bulkDeactivate/bulkRestore bypass validated()) — STILL OPEN
- Finding #56 (no max:N on bulk feedback/contact) — FIXED (confirmed in 10th audit)

**Why:** Full security audit passes.
**How to apply:** Reference these findings when reviewing future PRs touching auth, billing, or webhook handling. Findings #10 (UpdatePaymentMethodRequest), #14 (SSRF), and the bulkDeactivate/bulkRestore validated() bypass remain open.
