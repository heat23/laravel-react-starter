---
name: project_security_patterns
description: Security patterns, conventions, and known issues discovered during security audits of the laravel-react-starter codebase
type: project
---

Security infrastructure in place as of 2026-03-22 (seventh full audit pass):

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
- dangerouslySetInnerHTML: QR code uses DOMPurify with SVG profile; Blog/Show uses DOMPurify allowlist; JSON-LD scripts use `</script>` escaping only (no DOMPurify — documented as intentional to avoid JSON corruption); Pricing.tsx also uses </script> escaping (no DOMPurify — hardcoded data); FaqJsonLd component also uses </script> escaping only — all FAQ data is hardcoded static data from server-side PHP arrays, not user input
- .env is in .gitignore and is NOT tracked in git history; .env.example has only placeholder comments
- No hardcoded secrets found in config/ files
- BlogController::loadPost uses slug constrained by route regex `[a-z0-9-]+` which prevents path traversal via `..`
- QueryHelper::dateExpression validates column name against `[a-zA-Z_][a-zA-Z0-9_.]*` before interpolating into raw SQL — safe
- QueryHelper::whereLike uses parameterized LIKE with proper LIKE wildcard escaping — safe
- User $fillable includes is_admin and super_admin — but all controllers use explicit field arrays (not $request->all()), making this a latent risk rather than active exploit
- UpdateSettingRequest restricts setting keys to an allowlist (theme, timezone, onboarding_completed, sidebar_state) — no arbitrary key injection
- AdminUsersController::update passes only name+email (from AdminUpdateUserRequest) — no privilege escalation via update
- UTM data written to UserSetting::setValue at registration — keys come from middleware whitelist (utm_source etc), not user input
- ApiTokens: TokenController uses auth:sanctum + user relationship scoping — no cross-user token access
- WebhookEndpointController: all CRUD operations scoped to $request->user()->webhookEndpoints() — IDOR protected
- composer audit: no vulnerabilities; npm audit: no vulnerabilities (as of 2026-03-22)
- Session: secure+httponly in production, same_site=lax, encrypt in production — correctly configured
- IncomingWebhookController: uses $request->all() for payload storage but this is intentional — the full payload is stored in incoming_webhooks table for processing; signature is already verified by VerifyWebhookSignature middleware before this controller is reached
- BreadcrumbJsonLd component: breadcrumbs data is entirely server-controlled static data from controllers; no user input flows into the JSON-LD — confirmed safe
- AdminScheduleController: exposes schedule command strings, but these are registered at boot time from code — not user input
- Feedback model byType/byStatus scopes use parameterized WHERE not raw SQL — no injection risk even with unvalidated input
- LoginRequest has BOTH route-level throttle:10,1 AND app-level RateLimiter in ensureIsNotRateLimited (5 attempts with IP+email key) — double-layer brute force protection
- ExportController::users is scoped exclusively to the authenticated user's own record (WHERE id = request->user()->id) — no cross-user data exposure
- AdminFailedJobsController: bulkRetry passes UUIDs directly to Artisan::call('queue:retry', ['id' => [$uuid]]) — Artisan wraps these as arguments, not shell-interpolated, so no command injection
- AdminSystemController: uses Laravel Process facade (not shell_exec/exec), and runs 'node --version' which is a hardcoded string — no injection risk
- ProductAnalyticsService: $nameExpr (JSON_EXTRACT expression) is hardcoded PHP string based on DB driver, not user input — safe
- CustomerHealthService: whereRaw expression is hardcoded DB-driver-conditional expression — not user input, safe
- LifecycleService::getStageVelocity: raw DB::select() with string-interpolated $diffExpr — expression is hardcoded PHP logic based on DB driver, not user input; safe (confirmed again in 7th audit)
- UnsubscribeController: uses signed URL (hasValidSignature) for authentication of unauthenticated route — correct security pattern; throttled at 10/min
- NpsSurveyController: uses inline $request->validate() instead of a Form Request — validation is present and correct (score 0-10, comment nullable/max:500, survey_trigger allowlist) so no security gap, just a style inconsistency
- ConsentController: uses inline $request->validate() with proper regex constraints on version and timestamp — safe
- New email sequence commands (SendWelcomeSequence, SendReEngagementEmails, SendWinBackEmails, SendDunningReminders, SendOnboardingReminders, SendTrialEndingReminders, SendTrialNudges): all run as console commands only, no user-facing routes, no user-controlled input into queries
- SendWinBackEmails::alreadySentEmail uses a raw LIKE pattern against the notifications JSON column ("%\"email_number\":N[^0-9]%") — $emailNumber is an int from the hardcoded EMAIL_SCHEDULE constant, not user input; no injection risk
- LeadScoringService: all DB queries use parameterized bindings (DB::table()->where()), no raw SQL with user input
- EngagementScoringService: all DB queries are parameterized; scoreBatch/score methods receive User objects from command chunks, not user-controlled data
- CustomerHealthService: primeLoginCountCache/primeBillingCache use whereIn/pluck with integer user IDs from DB — no injection risk
- CRM lifecycle commands (QualifyLeads, ComputeUserScores, CheckExpiredTrials) run server-side as scheduled jobs, no user-controlled inputs

Known findings from audits:

First audit (2026-03-21):
1. MEDIUM: VerifyWebhookSignature — replay protection runs AFTER signature verification instead of BEFORE
2. MEDIUM: SwapPlanRequest coupon field — only has max:50 with no format constraint (SubscribeRequest has regex whitelist) [STILL UNADDRESSED]
3. MEDIUM: 2FA challenge POST has throttle:5,1 but no independent brute-force lockout in controller
4. LOW: AdminSystemController exposes PHP/OS/kernel/Laravel/package versions to all admin users (not just super_admin) [STILL UNADDRESSED]
5. LOW: PersonalDataExportController includes full IP addresses and user-agent strings in export

Second audit (2026-03-21):
6. MEDIUM: User model $fillable includes is_admin and super_admin — latent mass assignment risk if future code ever calls ->fill($request->all()) or ->update($request->all()) on the User model [STILL UNADDRESSED]
7. LOW: AdminFeedbackController::index uses raw $request->input('type') and $request->input('status') for query filters without form request validation (though these are passed to parameterized query scopes, not raw SQL) [STILL UNADDRESSED]
8. INFO: BlogController::loadPost reads from resources/content/blog/ — path traversal not possible due to route regex `[a-z0-9-]+` excluding dots and slashes

Third audit (2026-03-21):
9. MEDIUM: admin.tokens.revoke and admin.webhooks.endpoints.restore routes use inline abort_unless(isSuperAdmin()) instead of super_admin middleware — bypasses EnsureIsSuperAdmin audit logging for unauthorized access attempts [STILL UNADDRESSED]
10. MEDIUM: UpdatePaymentMethodRequest accepts any string for payment_method with no format constraint — should add regex:/^pm_[a-zA-Z0-9]+$/ to enforce Stripe PM ID format [STILL UNADDRESSED]
11. Note: Replay protection for non-Stripe webhooks (GitHub etc) is absent — $timestamp is null for all non-Stripe providers in VerifyWebhookSignature::extractTimestamp(), so the replay tolerance check is silently skipped
12. Note: All JSON-LD dangerouslySetInnerHTML in Pages/ use hardcoded static data — confirmed not an XSS risk
13. Note: LifecycleService.php uses a raw DB::select() with a string-interpolated diff expression ($diffExpr) — the expression itself is hardcoded PHP logic based on DB driver, not user input; safe

Fourth audit (2026-03-21):
14. MEDIUM: DispatchWebhookJob sends HTTP requests to user-controlled URLs with no SSRF protection — Laravel's `url:https,http` validator accepts private/internal IPs (127.0.0.1, 10.x.x.x, 169.254.169.254 etc). A user can register a webhook endpoint pointing to internal infrastructure. [STILL UNADDRESSED]
15. Note: AdminTokensController::revoke and AdminWebhooksController::restoreEndpoint still use inline abort_unless(isSuperAdmin()) — finding #9 remains unfixed
16. Note: composer audit and npm audit both return 0 vulnerabilities as of this audit pass

Fifth audit (2026-03-22):
17. Note: AdminSessionsController::destroy takes $userId as int route parameter and deletes sessions by user_id — no ownership check is needed here since this is an admin-only route behind auth+verified+admin+super_admin middleware
18. Note: SubscriptionController::applyRetentionCoupon uses coupon ID from config (plans.retention_coupon_id), not from user input — no coupon injection risk
19. MEDIUM: AdminFeedbackController::index uses plain Request (not a FormRequest) with no rate limiting, and passes raw $request->input('type') and $request->input('status') to query scopes without a form request — low direct risk but bypasses consistent validation pattern [STILL UNADDRESSED from finding #7]
20. Note: ConsentController.store validates all fields including timestamp/version with regex — safe
21. Note: HealthCheckController uses hash_equals for token comparison — timing-safe

Sixth audit (2026-03-22):
22. MEDIUM: AdminUsersController::store allows any admin (not just super_admin) to create a new admin user by passing is_admin=true. toggleAdmin (privilege escalation) requires super_admin middleware, but store (initial admin creation) only requires the regular admin role. Inconsistency allows a non-super-admin to create admins by using the create form.
23. MEDIUM (CONFIRMED UNADDRESSED): SwapPlanRequest coupon field has max:50 with no format regex (finding #2, still unaddressed). SubscribeRequest has regex:/^[a-zA-Z0-9_-]+$/ — the two endpoints are inconsistent.
24. MEDIUM (CONFIRMED UNADDRESSED): UpdatePaymentMethodRequest accepts any string for payment_method with no pm_ prefix format constraint (finding #10, still unaddressed).
25. MEDIUM (CONFIRMED UNADDRESSED): DispatchWebhookJob sends HTTP to user-controlled URLs with no SSRF protection (finding #14, still unaddressed). Laravel's url:https,http validator accepts private/internal IPs.
26. LOW (CONFIRMED UNADDRESSED): AdminSystemController exposes PHP/OS/kernel/Laravel/package versions to all admin users not just super_admin (finding #4, still unaddressed).
27. LOW (CONFIRMED UNADDRESSED): User model $fillable includes is_admin and super_admin — latent mass assignment risk (finding #6, still unaddressed).
28. Note: All dangerouslySetInnerHTML in Pages/Guides/*.tsx and Pages/Compare/*.tsx use hardcoded static data with </script> escaping only — confirmed not user-controlled, not an XSS risk.
29. Note: Security.tsx QR code uses DOMPurify with SVG profile — correct.
30. Note: AdminCreateUserRequest.authorize() checks isAdmin() not isSuperAdmin() — this is the authorization gate for admin user creation including the is_admin flag. Combined with finding #22, any admin can grant admin rights at creation time without super_admin.
31. Note: CaptureUtmParameters middleware uses a fixed whitelist of 5 param keys — no user-controlled key injection possible.
32. Note: IncomingWebhookController::extractEventType returns raw header/input value for unknown providers — but provider string is constrained by VerifyWebhookSignature config lookup (403 on unknown provider), so only known providers reach the controller. Safe.
33. Note: RoadmapController::vote uses parameterized DB queries scoped to user — no IDOR.
34. Note: No new hardcoded secrets found in new files (CRM lifecycle, lead scoring, analytics services).

Seventh audit (2026-03-22):
35. Note: New CRM lifecycle commands (QualifyLeads, ComputeUserScores, CheckExpiredTrials, SendWelcomeSequence, SendReEngagementEmails, SendWinBackEmails, SendDunningReminders, SendOnboardingReminders, SendTrialEndingReminders, SendTrialNudges, PruneReadNotifications) all run as scheduled console commands only — no user-facing routes, no user-controlled query input; safe.
36. Note: UnsubscribeController uses Laravel signed URL (hasValidSignature) for authorization — correct. Route is throttled at 10/min. User ID is constrained by whereNumber() route constraint.
37. Note: NpsSurveyController uses inline $request->validate() but validation is complete and correct (score 0–10, comment max:500, survey_trigger allowlist). No security gap — style inconsistency only.
38. Note: SendWinBackEmails::alreadySentEmail uses LIKE pattern on notifications JSON — $emailNumber is a hardcoded int constant, no user input in the pattern; safe.
39. Note: LeadScoringService, EngagementScoringService, CustomerHealthService, LifecycleService all use parameterized queries only; no raw SQL with user input.
40. Note: AdminUsersController::store finding #22 (any admin can create admin user) — CONFIRMED STILL UNADDRESSED. AdminCreateUserRequest::authorize() checks isAdmin() not isSuperAdmin().
41. CONFIRMED UNADDRESSED: Findings 2, 4, 6, 7/19, 9, 10, 14, 22 remain open as of 7th audit.

Eighth audit (2026-03-22):
42. Note: BlogController::parseMarkdown runs htmlspecialchars() first (line 158), then runs link regex (line 176) that builds raw <a href="$2"> from Markdown. Because htmlspecialchars() does NOT encode colons/slashes, a Markdown file containing [text](javascript:...) would produce an unsanitized href. However, all Markdown files are in resources/content/blog/ (server filesystem, admin-controlled only); no user input flows here. Not an active exploit — but DOMPurify's ALLOWED_ATTR: ['href'] on the client does filter javascript: URLs in most browsers. Documenting as INFO: design risk if ever extended to user-generated Markdown.
43. Note: User model $fillable now also includes 'lifecycle_stage', 'acquisition_channel', 'utm_source', 'utm_medium', 'utm_campaign', 'health_score', 'engagement_score', 'lead_score', 'marketing_opt_out' — all privileged scoring fields. Finding #6 (latent mass assignment) is now more severe because more scoring/lifecycle fields are fillable. STILL UNADDRESSED.
44. Note: AuditLogController::applyFilters uses manual LIKE escaping (str_replace '%' => '\\%') but does NOT escape underscore ('_'). This is a minor wildcard issue — '_' in a search term will match any single character. Not a security issue since it only affects query accuracy, not injection. Low confidence/LOW severity.
45. Note: AdminFeedbackController::update uses inline $request->validate() — but validation is complete and correct (Rule::in allowlists for status and priority). No security gap, style inconsistency only.
46. Note: AdminRoadmapController::store and ::update use inline $request->validate() — validations are correct with Rule::in allowlists. No security gap.
47. Note: AdminFailedJobsController::bulkRetry uses inline $request->validate() (not a FormRequest) — but correctly validates ids as array, max:100, string items. No security gap.
48. Note: AdminCacheController::flush uses inline $request->validate() with allowlist in:all,billing,tokens,... — correct, no injection risk.
49. Note: No new hardcoded secrets found in any files in this audit pass.
50. Note: HandleInertiaRequests shares is_super_admin via $user->isSuperAdmin() — this is intentional for frontend gating. Password hash, remember_token, and stripe keys are NOT in the shared props. Safe.
51. CONFIRMED UNADDRESSED: Findings 2, 4, 6, 7/19, 9, 10, 14, 22 remain open as of 8th audit.

Ninth audit (2026-03-31):
52. FIXED: Finding #22 (admin user creation privilege escalation) — AdminCreateUserRequest now gates the `is_admin` rule behind isSuperAdmin() check. Non-super-admins can no longer create admin users. AdminUsersController::store confirmed to only apply is_admin from validated data.
53. FIXED: Finding #7/19 (AdminFeedbackController::index used plain Request) — now uses AdminFeedbackIndexRequest FormRequest.
54. FIXED: Finding #4 (AdminSystemController visible to all admins) — route now has `->middleware('super_admin')` in routes/admin.php.
55. MEDIUM: AdminBulkDeactivateRequest validates ids max:100, but AdminUsersController::bulkDeactivate reads $request->input('ids') not $request->validated('ids'), bypassing the max:100 cap. Same bug in bulkRestore. An admin could send an unbounded array. [NEW, UNADDRESSED]
56. LOW: AdminBulkFeedbackRequest has no max:N on ids array — no upper bound. Same for AdminBulkContactSubmissionRequest. An admin could trigger a large lockForUpdate. [NEW, UNADDRESSED]
57. Note: AdminToggleActiveRequest and AdminToggleAdminRequest authorize() check isAdmin() but routes have super_admin middleware — route middleware is the real guard, FormRequest is cosmetically loose. Not a security gap.
58. Note: Command palette user search calls GET /admin/feature-flags/search-users via fetch() — GET is CSRF-exempt, route is behind admin middleware. Safe.
59. CONFIRMED UNADDRESSED: Findings 2, 6, 9, 10, 14 remain open as of 9th audit. Findings 4, 7/19, 22 now FIXED.

**Why:** Full security audit passes.
**How to apply:** Reference these findings when reviewing future PRs touching auth, billing, or webhook handling. Findings 2, 6, 9, 10, 14 are still unaddressed. Finding #55 (bulkDeactivate/bulkRestore bypass validated()) is new. Finding #56 (no max:N on bulk feedback/contact submission IDs) is new.
