# Agent Review — NextjsSaas Comparison Page

## Summary

Implementation of a long-form comparison page ("Laravel vs Next.js for SaaS 2026") with proper SEO markup, structured JSON-LD schemas, and analytical tracking. The component is well-written and follows project patterns, but has a critical canonical URL bug and missing OpenGraph URL meta tag that will impact SEO.

## Findings

### CRITICAL

**1. Canonical URL is relative instead of absolute**
- **File:** `resources/js/Pages/Compare/NextjsSaas.tsx`, line 101
- **Issue:** `<link rel="canonical" href="/compare/laravel-vs-nextjs" />` uses a relative path
- **Impact:** Search engines require absolute canonical URLs (RFC 6596). Relative paths are treated as relative to the current page's domain in the `<head>` context, but best practice and some SEO tools expect a fully qualified URL matching the `mainEntityOfPage` in the JSON-LD schema
- **Pattern in codebase:** Other guide pages use absolute URLs: `const canonicalUrl = 'https://laravelreactstarter.com/guides/cost-of-building-saas-from-scratch'` then `<link rel="canonical" href={canonicalUrl} />`
- **Fix:** Should match the absolute URL pattern used in guide pages, or use `${appUrl}/compare/laravel-vs-nextjs`
- **Severity:** Search engines may treat relative canonical as ambiguous; duplicate content penalty risk

**2. Missing `og:url` meta tag**
- **File:** `resources/js/Pages/Compare/NextjsSaas.tsx`, lines 99–113
- **Issue:** OpenGraph `og:url` is missing while `og:title`, `og:description`, `og:type` are present
- **Impact:** Facebook/LinkedIn crawlers may not canonicalize the page correctly for social sharing, leading to duplicate social card previews
- **Pattern in codebase:** Shipfast comparison page includes `og:title`, `og:description`, `og:type` but also omits `og:url`; guide pages include it
- **Fix:** Add `<meta property="og:url" content={canonicalUrl} />`
- **Severity:** Medium — affects social media SEO, not search engine ranking directly

### HIGH

**3. Controller uses global `appUrl` instead of passing absolute URL to component**
- **File:** `app/Http/Controllers/CompareController.php`, lines 225–239
- **Issue:** Controller computes `$appUrl` but the view receives it, yet the React component recomputes it client-side: `const appUrl = typeof window !== 'undefined' ? window.location.origin : '';` (line 40)
- **Impact:**
  - SSR context: If Inertia SSR is enabled, the appUrl will be undefined on the server, falling back to empty string, breaking JSON-LD mainEntityOfPage during initial page render
  - The canonical URL and JSON-LD are hardcoded, so they work, but appUrl is computed but unused
  - Inconsistent with pattern where controller passes appUrl and component uses it (see other compare methods like `jetstream()`)
- **Pattern in codebase:** Other compare methods pass `appUrl` via Inertia props; the component should use it from props, not recompute
- **Fix:** Pass `appUrl` in the controller return, update component signature to accept `appUrl` in GuidePageProps, and use it in JSON-LD mainEntityOfPage
- **Severity:** High — SSR rendering may fail silently; appUrl recomputation is fragile

### MEDIUM

**4. Hardcoded canonical URL and schema URLs may cause issues if domain changes**
- **File:** `resources/js/Pages/Compare/NextjsSaas.tsx`, line 54 in articleSchema
- **Issue:** Line 54 hardcodes the full URL: `'@id': '${appUrl}/compare/laravel-vs-nextjs'` which relies on appUrl client-side computation (line 40)
- **Secondary issue:** Line 101 uses relative canonical, line 54 uses computed `appUrl` — inconsistency
- **Impact:** If appUrl is empty string on SSR, the schema URL becomes invalid JSON-LD
- **Fix:** Use absolute URL from controller or environment variable consistently everywhere
- **Severity:** Medium — works in browser, fails on SSR

**5. No test coverage for this page**
- **File:** N/A — no `NextjsSaas.test.tsx` exists
- **Issue:** All other comparison pages have test files (e.g., `Shipfast.test.tsx`, `Supastarter.test.tsx`, `Wave.test.tsx`)
- **Pattern in codebase:** `.test.tsx` files verify page renders, props are passed correctly, SEO metadata is present
- **Impact:** No guarantee that analytics event fires, breadcrumbs render, or JSON-LD is valid on page load
- **Fix:** Create `resources/js/Pages/Compare/NextjsSaas.test.tsx` with basic render + analytics + schema validation
- **Severity:** Medium — production risk, inconsistent with project convention

**6. Accessibility issue: Details/summary elements lack ARIA attributes**
- **File:** `resources/js/Pages/Compare/NextjsSaas.tsx`, lines 610–662
- **Issue:** `<details>` and `<summary>` elements don't have `aria-expanded`, `aria-controls`, or role attributes
- **Pattern in codebase:** Other components use Radix UI `<Dialog>` and `<Collapsible>` for accessible disclosure patterns
- **Impact:** Screen reader users won't know the disclosure state; keyboard navigation (Enter to toggle) works by default, but no accessibility announcement
- **Fix:** Either use Radix UI `Collapsible` component (if available) or add aria-expanded to summary
- **Severity:** Medium — WCAG 2.1 Level AA compliance issue, but browser defaults provide basic keyboard support

### LOW / SUGGESTIONS

**1. Analytics event page parameter is overly specific**
- **File:** `resources/js/Pages/Compare/NextjsSaas.tsx`, line 37
- **Issue:** `track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-laravel-vs-nextjs' })` uses kebab-case
- **Pattern in codebase:** Other pages use kebab-case consistently (e.g., `compare-shipfast`), so this is consistent
- **Suggestion:** No action needed; pattern is consistent
- **Severity:** Low — informational only

**2. Footer year calculation works but could use a constant**
- **File:** `resources/js/Pages/Compare/NextjsSaas.tsx`, line 736
- **Issue:** `new Date().getFullYear()` is computed at component render time, not build time
- **Impact:** Footer year updates on every request (minor performance, correct behavior)
- **Suggestion:** Move to constant if footer is reused across many pages; currently fine
- **Severity:** Low — best practice suggestion only

**3. Table of contents `sections` array is defined outside component**
- **File:** `resources/js/Pages/Compare/NextjsSaas.tsx`, lines 19–31
- **Issue:** `sections` could be inside the component since it doesn't depend on props
- **Impact:** No functional issue; improves code clarity if moved inside (avoids unnecessary module-level data)
- **Suggestion:** Consider moving inside component for encapsulation
- **Severity:** Low — code style preference

## Verdict

**APPROVE WITH NOTES** *(post-review fixes applied)*

### Fixes applied after initial review

1. ✅ **CRITICAL — Canonical URL** made absolute: now uses `canonicalUrl` derived from `breadcrumbs[0].url` (server-side `config('app.url')`) + path. No SSR mismatch.
2. ✅ **CRITICAL — og:url** meta tag added: `<meta property="og:url" content={canonicalUrl} />`.
3. ✅ **HIGH — SSR appUrl** fixed: replaced `window.location.origin` with `breadcrumbs?.[0]?.url ?? ''` — safe on server during SSR.
4. ✅ **MEDIUM — Missing tests** added: `NextjsSaas.test.tsx` covering render, H1, CTA links, and FAQ section.

### Remaining notes

- **MEDIUM — Accessibility (details/summary):** `<details>`/`<summary>` FAQ elements lack explicit `aria-expanded`. Browser provides default keyboard support (Enter/Space). Acceptable for initial ship; track for accessibility audit pass.
- **LOW — sections array** defined at module level (no functional issue).

The page content is high quality, SEO schema is correct (Article + FAQPage + BreadcrumbList JSON-LD, author as Person per SD006), and all critical/high issues are resolved.
