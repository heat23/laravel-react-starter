/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

## Findings to Fix

### Fix 1: A11Y-001 — No skip-to-content link (P3, 0.25h est.)
**Problem:** Welcome page and authenticated layouts lack a skip-to-content link. WCAG 2.1 Level A violation (2.4.1 Bypass Blocks). The welcome page has `<main id="main-content">` but no skip link.
**Files:** resources/js/Pages/Welcome.tsx (line ~91), resources/js/Layouts/ (authenticated layout)
**Implementation:** Add as the first focusable element in both layouts:
```tsx
<a href="#main-content" className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-background focus:text-foreground focus:border focus:border-border focus:rounded-md">
  Skip to content
</a>
```
**Verify:** Tab into page, first focus shows skip link, Enter scrolls to main content.

### Fix 2: A11Y-002 — Feature icons lack aria-hidden (P3, 0.25h est.)
**Problem:** Welcome page feature icons (Shield, Layers3, Zap) rendered without aria-hidden. Screen readers may announce icon names redundantly since text labels follow.
**Files:** resources/js/Pages/Welcome.tsx (line ~154-155)
**Implementation:** Add `aria-hidden="true"` to the icon component renders.
**Verify:** Screen reader test or `npm run build` to verify no errors.

### Fix 3: COPY-001 — Generic billing error messages (P3, 1h est.)
**Problem:** Billing errors use generic "Unable to process your request. Please try again." without differentiation.
**Files:** app/Http/Controllers/Billing/SubscriptionController.php (line ~88)
**Implementation:** Add error message mapping for common Stripe error codes:
- `card_declined` → "Your card was declined. Please try a different payment method."
- `expired_card` → "Your card has expired. Please update your payment method."
- `processing_error` → "There was an error processing your card. Please try again in a few minutes."
- Default → "Unable to process your request. Please try again or contact support."
**Verify:** `php artisan test --filter=Subscription`

### Fix 4: COPY-002 — Legal template disclaimer too subtle (P3, 0.5h est.)
**Problem:** Legal content modal disclaimer is text-xs text-muted-foreground, easy to miss.
**Files:** resources/js/Components/legal/LegalContentModal.tsx (lines 70-73)
**Implementation:** Replace the small text with an Alert component (variant="destructive" or variant="warning") that clearly states the content needs legal review before deployment.
**Verify:** Visual check.

### Fix 5: UX-001 — Charts page loading state (P3, 1h est.)
**Problem:** Charts page may not show loading skeleton during data fetch.
**Files:** resources/js/Pages/Dashboard/Charts.tsx (if exists, or similar chart page)
**Implementation:** Add Skeleton loading states for chart components while data loads. Use the existing Skeleton component pattern from ui/.
**Verify:** Visual check navigating to charts page.

### Fix 6: DS-001 — Container utility max-width (P3, 0.25h est.)
**Problem:** Custom .container class in app.css lacks max-width constraint.
**Files:** resources/css/app.css (lines 284-287)
**Implementation:** Add `max-width: 80rem;` to the .container utility class.
**Verify:** Check on ultrawide display that content doesn't stretch.

### Fix 7: SEO-002 — Unused spatie/laravel-sitemap dependency (P3, 0.5h est.)
**Problem:** spatie/laravel-sitemap is in composer.json but the sitemap is generated manually via SeoController.
**Files:** composer.json (line 16)
**Implementation:** Remove `"spatie/laravel-sitemap": "^7.3"` from composer.json and run `composer update --lock`.
**Verify:** `composer audit && php artisan test --filter=SeoTest`

### Fix 8: DEBT-001 — package.json template placeholder (P3, 0.1h est.)
**Problem:** package.json name is still "{{PROJECT_NAME}}".
**Files:** package.json (line 2)
**Implementation:** Replace with "laravel-react-starter" or the actual project name.
**Verify:** `npm run build`

### Fix 9: ADM-005 — No verified/unverified filter on admin user index (P3, 0.5h est.)
**Problem:** Admin user list shows verified status but can't filter by it.
**Files:** app/Http/Requests/Admin/AdminUserIndexRequest.php, app/Http/Controllers/Admin/AdminUsersController.php, resources/js/Pages/Admin/Users/Index.tsx
**Implementation:** Add 'verified' filter to AdminUserIndexRequest rules, apply whereNull/whereNotNull('email_verified_at') in controller, add Select filter in UI.
**Verify:** `php artisan test --filter=AdminDashboardTest`

### Fix 10: ADM-006 — Subscription list rows not linkable to detail (P3, 0.25h est.)
**Problem:** Subscription list table links user name to user profile but no link to subscription detail page, even though the route exists.
**Files:** resources/js/Pages/Admin/Billing/Subscriptions.tsx (lines 113-136)
**Implementation:** Add a "View" link/button in each subscription row pointing to `/admin/billing/subscriptions/${sub.id}`.
**Verify:** Visual check + `npm run build`

## After All Fixes

```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "fix(ux): a11y skip-link, billing errors, loading states, admin filters, dependency cleanup"`
