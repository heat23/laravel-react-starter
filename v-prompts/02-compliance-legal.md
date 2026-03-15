/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

## Findings to Fix

Work through these in order. For each one: write the test first (TDD for backend, test-after for UI), implement the fix, run the verification command, then move to the next.

### Fix 1: LEGAL-001 — No cookie consent mechanism (P1, 4h est.)
**Problem:** Google Analytics loads unconditionally in production via resources/views/app.blade.php. GDPR/ePrivacy requires consent before non-essential cookies. No cookie consent banner exists anywhere.
**Files:** resources/views/app.blade.php (lines 22-31)
**Test first:** Create resources/js/Components/CookieConsent.test.tsx — test that: (1) banner renders on first visit, (2) accepting consent stores preference in localStorage, (3) declining hides analytics, (4) banner does not show on return visit with consent.
**Implementation:**
1. Create resources/js/Components/CookieConsent.tsx — a banner at the bottom of the page with Accept/Decline buttons. Store consent in localStorage key 'cookie_consent'.
2. In app.blade.php, wrap the GA4 script in a check: only inject if a consent flag is present. Use a small inline script that checks localStorage before loading gtag.
3. The CookieConsent component should call `window.location.reload()` on accept to trigger GA loading, or dynamically inject the script tag.
**Verify:** `npm test -- --run --filter=CookieConsent`

### Fix 2: LEGAL-002 — No comprehensive GDPR personal data export (P2, 6h est.)
**Problem:** ExportController only exports name, email, created_at. GDPR Article 15/20 requires export of ALL personal data: profile, settings, audit logs, social accounts, tokens metadata, webhook endpoints.
**Files:** app/Http/Controllers/ExportController.php (lines 14-17)
**Test first:** tests/Feature/ExportTest.php — add test: `it('exports all personal data categories')`. Create user with settings, audit logs, social account. Assert export contains all categories.
**Implementation:**
1. Create app/Http/Controllers/PersonalDataExportController.php (or extend ExportController).
2. Gather: users (full row), user_settings, audit_logs (for this user), social_accounts, personal_access_tokens (name, created_at, last_used — NOT token hash), webhook_endpoints.
3. Return as JSON download (ZIP if large). Include metadata about what's included.
4. Add route: Route::get('/profile/export-data', [PersonalDataExportController::class, 'export'])->middleware(['auth', 'verified', 'throttle:3,60']);
5. Add link in profile/settings page.
**Verify:** `php artisan test --filter=ExportTest`

### Fix 3: LEGAL-003 — Legal templates are placeholder content (P2, 2h est.)
**Problem:** LegalContentModal has template ToS/Privacy with easy-to-miss disclaimer. Need stronger warning + standalone pages.
**Files:** resources/js/Components/legal/LegalContentModal.tsx (lines 36-74), routes/web.php
**Test first:** tests/Feature/LegalPagesTest.php — test that /terms and /privacy routes return 200.
**Implementation:**
1. Create resources/js/Pages/Legal/Terms.tsx and Privacy.tsx with the legal content rendered as full pages.
2. Add routes in web.php: Route::get('/terms', ...) and Route::get('/privacy', ...).
3. Make the disclaimer in LegalContentModal more prominent: use an Alert component with variant "destructive" instead of text-xs.
4. Add the legal pages to the sitemap in SeoController.
**Verify:** `php artisan test --filter=LegalPagesTest`

### Fix 4: FDBK-03 — No cancellation reason collection (P2, 4h est.)
**Problem:** CancelSubscriptionDialog does not ask why the user is canceling. This is the most actionable feedback data.
**Files:** resources/js/Components/billing/CancelSubscriptionDialog.tsx, app/Http/Controllers/Billing/SubscriptionController.php (line ~92-143), app/Http/Requests/Billing/CancelSubscriptionRequest.php
**Test first:** tests/Feature/Billing/CancelSubscriptionTest.php — add test: `it('stores cancellation reason in audit log')`. Cancel with reason, assert audit log contains reason.
**Implementation:**
1. Add a required `reason` field to CancelSubscriptionRequest: `'reason' => ['required', 'string', 'in:too_expensive,missing_features,competitor,not_needed,other']` and optional `reason_detail`.
2. In SubscriptionController cancel handler, log the reason via AuditService.
3. In CancelSubscriptionDialog, add a RadioGroup with predefined reasons + optional textarea for 'other'.
4. Pass reason in the router.post/patch call.
**Verify:** `php artisan test --filter=CancelSubscription`

### Fix 5: LAUNCH-004 — Legal pages as standalone URLs (P3, covered by Fix 3)
Already addressed in Fix 3 above.

## After All Fixes

Run the full verification suite:
```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "fix(compliance): cookie consent, GDPR data export, legal pages, cancellation reasons"`
