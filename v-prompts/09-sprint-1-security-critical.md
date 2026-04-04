# Sprint 1: Security Critical Fixes

**Source:** `AUDIT_FULL_REPORT_76c2377f-e028-444b-9943-bc2712a067f3.md`
**Priority:** P0 — Launch Blockers
**Estimated Effort:** 1-2 days

---

## Task 1: Super Admin Gate on Feature Flag Overrides (SEC-CRIT-001)

```
/v fix SEC-CRIT-001: In app/Http/Controllers/Admin/AdminFeatureFlagController.php, the updateGlobal() method (lines 39-59) accepts any admin user. Add abort_unless($request->user()?->isSuperAdmin(), 403) at the top of updateGlobal(), updateUser(), and deleteGlobal() methods. Also add the same check to deleteUser(). Write Pest tests verifying:
1. Super admin can update global feature flag overrides
2. Regular admin gets 403 on global override attempt
3. Super admin can update per-user overrides
4. Regular admin gets 403 on per-user override attempt
```

## Task 2: Webhook Replay Protection for All Providers (SEC-CRIT-002)

```
/v fix SEC-CRIT-002: In app/Http/Middleware/VerifyWebhookSignature.php, replay protection (lines 47-53) only works for Stripe webhooks. For GitHub and custom providers, $timestamp is null and protection is bypassed. Fix by:
1. For GitHub: extract timestamp from X-GitHub-Delivery header and enforce 300s tolerance
2. For custom providers: require X-Webhook-Timestamp header, enforce 300s tolerance
3. Add nonce deduplication: cache webhook request IDs (X-GitHub-Delivery, X-Webhook-Request-Id) for 10 minutes, reject duplicates
4. If no timestamp or nonce is available, reject with 400
Write Pest tests for: replay with old timestamp, replay with duplicate nonce, valid webhook from each provider type, missing headers.
```

## Task 3: DOMPurify on All JSON-LD Components (SEC-CRIT-004)

```
/v fix SEC-CRIT-004: These files use dangerouslySetInnerHTML with only basic </script> escaping instead of DOMPurify:
- resources/js/Components/seo/BreadcrumbJsonLd.tsx (line 20)
- resources/js/Components/seo/FaqJsonLd.tsx (line 30)
- resources/js/Pages/Guides/TwoFactorGuide.tsx
- resources/js/Pages/Guides/LaravelSaasGuide.tsx
- resources/js/Pages/Guides/SaasStarterKitComparison.tsx
- resources/js/Pages/Guides/TenancyArchitectureGuide.tsx
- resources/js/Pages/Guides/BuildVsBuyGuide.tsx
- resources/js/Pages/Guides/WebhookGuide.tsx
- resources/js/Pages/Guides/FeatureFlagsGuide.tsx
- resources/js/Pages/Compare/NextjsSaas.tsx
- resources/js/Pages/Compare/Larafast.tsx

Replace the .replace(/<\/script>/gi, ...) pattern with DOMPurify.sanitize() in all files. Follow the existing pattern from Blog/Pricing pages that already use DOMPurify correctly. Create a shared helper like sanitizeJsonLd() if one doesn't exist. Update tests to verify sanitization.
```

## Task 4: CSV Injection Prevention in Admin Exports (SEC-HIGH-003)

```
/v fix SEC-HIGH-003: In app/Http/Controllers/Admin/AdminBillingController.php (lines 132-165) and any other admin export methods, CSV data is streamed without sanitizing control characters. If a user_name starts with =, +, -, or @, Excel will execute it as a formula.

Fix: Add CSV sanitization to all fputcsv calls — prefix cells starting with =, +, -, @ with a single quote ('). Check app/Support/CsvExport.php if it exists and add sanitization there as the centralized location.

Also add audit logging for every export: AuditService::log('admin_export', ['type' => 'subscriptions', 'filters' => $validated, 'row_count' => $count]).

Write Pest tests verifying: cells with = prefix are escaped, normal cells are unchanged, export is audit-logged.
```

## Task 5: Rate Limiting on Billing Operations (SEC-HIGH-004)

```
/v fix SEC-HIGH-004: Billing endpoints in routes/web.php (subscribe, cancel, swap, updateQuantity, resume) have no rate limiting. Add throttle middleware:
- subscribe: throttle:3,1 (3 per minute)
- cancel: throttle:3,1
- swap: throttle:5,1
- updateQuantity: throttle:5,1
- resume: throttle:3,1
- checkout: throttle:5,1

Register named rate limiters in AppServiceProvider if needed. Write Pest test verifying 429 response on exceeding limit.
```

## Task 6: SSRF Prevention on Webhook URLs (SEC-HIGH-005)

```
/v fix SEC-HIGH-005: In app/Http/Requests/Webhook/CreateWebhookEndpointRequest.php (line 19), webhook URL validation allows localhost and private IPs. Create a custom validation rule NotPrivateUrl that blocks:
- 127.0.0.0/8, ::1 (loopback)
- 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16 (RFC1918)
- 169.254.0.0/16, fe80::/10 (link-local)
- 0.0.0.0
- localhost, *.local hostnames

Apply to CreateWebhookEndpointRequest and UpdateWebhookEndpointRequest. Write Pest tests for each blocked range and valid external URLs.
```

## Task 7: Rate Limit Admin Access Attempts (SEC-HIGH-006)

```
/v fix SEC-HIGH-006: In app/Http/Middleware/EnsureIsAdmin.php (lines 19-25), unauthorized admin access is logged but not rate-limited. Add RateLimiter::hit() with key based on user ID or IP, allowing 10 attempts per 60 seconds. After exceeding, return 429 instead of 403. Write Pest test verifying rate limiting kicks in after 10 failed attempts.
```

## Task 8: npm audit fix (SEC-HIGH-012)

```
Run npm audit fix to resolve 3 high-severity vulnerabilities: lodash-es (prototype pollution), picomatch (method injection/ReDoS), brace-expansion (memory exhaustion). Then run npm run build && npm test to verify no regressions.
```
