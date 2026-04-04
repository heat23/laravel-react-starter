# Comprehensive SaaS Audit Report

**Session:** `bac592f1-f449-4fc2-a4c7-30139f0127e4`
**Date:** 2026-04-04
**Branch:** `main` @ `c1f3b44`
**Depth:** Comprehensive
**Stack:** Laravel 12 + Inertia.js v2 + React 18 + TypeScript + Tailwind CSS v4

---

## Executive Summary

**Overall Assessment: Production-ready with targeted hardening needed.**

The codebase demonstrates strong security practices, comprehensive feature coverage, and well-structured architecture. No critical vulnerabilities were found. The 5 high-severity findings center around input validation gaps in billing endpoints and CI pipeline hardening — all fixable with low-to-medium effort before GA launch.

| Severity | Count | Action Required |
|----------|-------|-----------------|
| Critical | 0 | - |
| High | 5 | Fix before launch |
| Medium | 14 | Fix before GA or document risk acceptance |
| Low | 12 | Address in post-launch hardening |
| Info | 7 | No action needed |
| **Total** | **38** | |

### Strengths

- **Security posture is strong:** Rate limiting on all sensitive endpoints, DOMPurify on all user-content rendering, CSP with nonces, CSRF protection with only the necessary Stripe webhook exception, comprehensive audit logging.
- **Billing is production-grade:** Redis-locked subscription mutations, proper eager loading before Cashier methods, DB transactions around all mutations, Stripe error handling with user-friendly messages.
- **Admin panel is well-secured:** Role separation (admin vs super_admin), feature-gated sections, impersonation safeguards, encrypted admin session data.
- **CI pipeline is comprehensive:** 6 parallel jobs covering PHP tests, JS tests, build, quality, security audit, and E2E tests.
- **Architecture is clean:** Model::preventLazyLoading(), proper $fillable declarations, FormRequests for validation, service classes for complex logic.

---

## Findings by Priority

### HIGH (5 findings) — Fix Before Launch

#### SEC-001: Resume endpoint missing FormRequest validation
**File:** `app/Http/Controllers/Billing/SubscriptionController.php:241`

The `resume()` method accepts a raw `Request` instead of a dedicated FormRequest. All other billing mutations use dedicated FormRequests — this inconsistency could allow unexpected request body content.

**Fix:** Create `ResumeSubscriptionRequest` FormRequest.

---

#### SEC-002: Retention coupon endpoint missing FormRequest validation
**File:** `app/Http/Controllers/Billing/SubscriptionController.php:406`

`applyRetentionCoupon()` uses raw Request. While coupon ID comes from config, the method lacks formal validation.

**Fix:** Create `ApplyRetentionCouponRequest` FormRequest.

---

#### SEC-003: No password confirmation on billing-critical mutations
**File:** `app/Http/Controllers/Billing/SubscriptionController.php`

Cancel, swap, subscribe, and quantity update routes do not require password re-confirmation. Session hijacking could trigger subscription changes without explicit user verification.

**Fix:** Add `password.confirm` middleware to destructive billing routes (cancel, swap at minimum).

---

#### SEC-004: Health check token transmittable via query string
**File:** `config/health.php:5`

`HEALTH_ALLOW_QUERY_TOKEN` allows the health check token in URL query parameters, which leak through access logs, proxy logs, and Referer headers.

**Fix:** Ensure `HEALTH_ALLOW_QUERY_TOKEN=false` in production. Add a runtime warning in `HealthCheckController` if enabled in production environment.

---

#### SEC-005: CSP style-src uses unsafe-inline
**File:** `app/Http/Middleware/SecurityHeaders.php:53`

`style-src 'unsafe-inline'` weakens CSS injection protection. Required by Tailwind JIT + Radix UI but reduces defense-in-depth.

**Fix:** Document risk acceptance. Investigate nonce-based style injection for production. Consider stricter CSP for authenticated pages.

---

### MEDIUM (14 findings) — Fix Before GA

#### Security (3)

| ID | Title | File | Effort |
|----|-------|------|--------|
| SEC-006 | Webhook endpoint allows HTTP URLs | `CreateWebhookEndpointRequest.php:19` | Low |
| SEC-007 | Incoming webhook provider not validated | `IncomingWebhookController.php:17` | Low |
| SEC-008 | Webhook secret exposed in show endpoint | `WebhookEndpointController.php:82` | Medium |

#### Billing (3)

| ID | Title | File | Effort |
|----|-------|------|--------|
| BIL-001 | Lifecycle transition failures silently swallowed | `BillingService.php:96` | Low |
| BIL-002 | Checkout session created outside Redis lock | `BillingService.php:44` | Low |
| BIL-003 | Retention coupon can be applied repeatedly | `SubscriptionController.php:406` | Medium |

#### Data Integrity (2)

| ID | Title | File | Effort |
|----|-------|------|--------|
| DATA-001 | Contact notification sent synchronously | `ContactController.php:49` | Low |
| DATA-002 | Raw SQL in LifecycleService | `LifecycleService.php:111` | Medium |

#### Frontend (2)

| ID | Title | File | Effort |
|----|-------|------|--------|
| FE-001 | JSON-LD uses replace instead of DOMPurify | `FaqJsonLd.tsx:30` | Low |
| FE-002 | TODO/FIXME in production code | `TwoFactorChallenge.tsx` | Low |

#### Infrastructure (4)

| ID | Title | File | Effort |
|----|-------|------|--------|
| INFRA-001 | PHPStan only runs on PRs | `ci.yml:230` | Low |
| INFRA-002 | npm audit high-severity is non-blocking | `ci.yml:299` | Low |
| INFRA-003 | JS test coverage step is non-blocking | `ci.yml:155` | Low |
| INFRA-004 | No automated dependency update tooling | - | Low |

#### Testing (2)

| ID | Title | File | Effort |
|----|-------|------|--------|
| TEST-001 | TestCase disables CSRF globally | `tests/TestCase.php:30` | Medium |
| TEST-002 | Rate limit bypass in 2FA test | `TwoFactorRecoveryTest.php:57` | Low |

---

### LOW (12 findings) — Post-Launch Hardening

| ID | Title | Category |
|----|-------|----------|
| BIL-004 | Payment method update not Redis-locked | Billing |
| DATA-003 | AdminBillingStatsService DB::raw patterns | Data |
| FE-003 | Blog allows 'class' in DOMPurify allowlist | Frontend |
| FE-004 | SVG QR rendering via dangerouslySetInnerHTML | Frontend |
| FE-005 | No React error boundary | Frontend |
| FE-006 | Inconsistent loading state patterns | Frontend |
| INFRA-005 | Bundle size check non-blocking | Infra |
| INFRA-006 | E2E tests duplicate build step | Infra |
| INFRA-007 | No branch protection rules | Infra |
| TEST-003 | SQLite/MySQL test driver mismatch | Testing |
| TEST-004 | Mutation testing not in CI | Testing |
| TEST-005 | No CSRF protection verification tests | Testing |

---

### INFO (7 findings) — No Action Required

| ID | Title | Assessment |
|----|-------|------------|
| PERF-003 | preventLazyLoading configured correctly | Well-implemented |
| ARCH-001 | Feature flag system with DB overrides | Well-architected |
| ARCH-002 | Admin panel role-based access | Correctly implemented |
| INFO-001 | CSRF exception scoped to Stripe | Correct |
| INFO-002 | Comprehensive rate limiting | Thorough |
| INFO-003 | Impersonation safeguards | Well-implemented |
| INFO-004 | CI pipeline with 6 jobs | Well-structured |

---

## Recommended Fix Order

### Sprint 1 (Pre-Launch) — 1-2 days
1. **SEC-001 + SEC-002:** Create missing FormRequests (30 min)
2. **SEC-004:** Enforce HEALTH_ALLOW_QUERY_TOKEN=false in production (15 min)
3. **BIL-001:** Add logging to lifecycle transition catch blocks (15 min)
4. **DATA-001:** Queue contact notification (15 min)
5. **SEC-006:** Restrict webhook URLs to HTTPS (10 min)
6. **SEC-007:** Add provider whitelist to incoming webhook route (10 min)
7. **FE-002:** Resolve TODO in TwoFactorChallenge.tsx (15 min)

### Sprint 2 (Pre-GA) — 2-3 days
1. **SEC-003:** Add password confirmation to billing mutations (2 hrs)
2. **SEC-008:** Stop exposing webhook secrets in show endpoint (1 hr)
3. **BIL-003:** Prevent repeated retention coupon application (1 hr)
4. **TEST-001:** Remove global CSRF disable, add targeted bypasses (2 hrs)
5. **INFRA-001-004:** CI pipeline hardening (1 hr)

### Sprint 3 (Post-GA) — Ongoing
1. Frontend hardening (error boundaries, loading states)
2. Mutation testing in CI
3. Branch protection rules
4. Automated dependency updates

---

## Files Audited

| Domain | Files | Key Observations |
|--------|-------|-----------------|
| Controllers | 45+ | Consistent FormRequest usage except 2 billing endpoints |
| Services | 18 | BillingService well-locked; LifecycleService uses raw SQL |
| Middleware | 10+ | Comprehensive security, rate limit, and feature gate stack |
| Routes | 4 files | Well-organized, feature-gated, rate-limited |
| Frontend | 344 files | DOMPurify used consistently, minor inconsistencies |
| Tests | 192 files | Good coverage, CSRF globally disabled |
| CI/CD | 1 workflow | 6 jobs, some non-blocking checks |
| Config | 10+ files | Well-structured, env-driven |

---

*Generated by comprehensive SaaS audit — session bac592f1-f449-4fc2-a4c7-30139f0127e4*
