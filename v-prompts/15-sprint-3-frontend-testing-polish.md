# Sprint 3: Frontend & Testing Polish (~3-4 days)
**Source:** audit-full-results_bac592f1.json (2026-04-04)
**Findings:** FE-003-006, TEST-002-005, PERF-001-002, INFRA-005-007

---

## Prompt 1: Add React Error Boundary (FE-005)

```
/v Add a top-level React error boundary to catch rendering errors.

1. Create resources/js/Components/ErrorBoundary.tsx:
   - Class component (error boundaries require class components)
   - Catches render errors, shows user-friendly fallback with "Something went wrong" message and "Reload Page" button
   - Logs error to console in development
   - Uses the project's semantic color tokens (bg-background, text-foreground)
   - Full-page fallback, not inline

2. Wrap the app entry point with ErrorBoundary:
   - In resources/js/app.tsx (or wherever createInertiaApp is called)
   - ErrorBoundary should wrap the page component render

3. Write a Vitest test:
   - Component that throws renders the error fallback
   - Reload button is present
```

---

## Prompt 2: Add CSRF Verification Tests (TEST-005)

```
/v Add integration tests that verify CSRF protection on critical routes.

Create tests/Feature/CsrfProtectionTest.php with Pest tests:

1. Billing routes require CSRF:
   it('rejects billing cancel without CSRF token', function () {
       $user = User::factory()->create();
       // Use raw HTTP client without the test helper's automatic CSRF
       $response = $this->call('POST', '/billing/cancel', [], [], [], [
           'HTTP_ACCEPT' => 'text/html',
       ]);
       expect($response->status())->toBe(419);
   });

2. Admin mutation routes require CSRF:
   - POST /admin/users (create)
   - PATCH /admin/users/{id}/toggle-admin

3. Profile deletion requires CSRF:
   - DELETE /profile

4. Auth routes require CSRF:
   - POST /login
   - POST /register

Note: These tests should NOT use withoutMiddleware(ValidateCsrfToken::class). The point is to verify CSRF IS active.
```

---

## Prompt 3: Add Rate Limit Verification Test (TEST-002)

```
/v Add a test verifying rate limiting on the 2FA challenge endpoint.

In tests/Feature/TwoFactor/, add a test (can be in existing file or new):

it('rate limits 2FA challenge attempts', function () {
    // Setup a user with 2FA enabled
    $user = User::factory()->create();
    // Enable 2FA for user (follow existing test setup patterns)
    
    // Make requests up to the rate limit
    for ($i = 0; $i < 10; $i++) {
        $this->post(route('two-factor.challenge'), ['code' => '000000']);
    }
    
    // Next request should be throttled
    $response = $this->post(route('two-factor.challenge'), ['code' => '000000']);
    expect($response->status())->toBe(429);
});
```

---

## Prompt 4: Implement Webhook Deliveries Pagination (PERF-002)

```
/v Replace take(50) with proper cursor pagination in the webhook deliveries endpoint.

In app/Http/Controllers/Api/WebhookEndpointController.php::deliveries():
1. Replace ->take(config(..., 50))->get() with ->cursorPaginate(config('pagination.api.webhook_deliveries', 50))
2. Return the paginated response as JSON with cursor links:
   return response()->json($deliveries);
   (Laravel's cursor paginator automatically includes next_cursor and prev_cursor)

3. Update the map() to work with the paginated collection

Write a Pest test:
- Create 60 webhook deliveries for an endpoint
- GET deliveries -> returns 50 with pagination cursor
- GET deliveries with cursor -> returns remaining 10
```

---

## Prompt 5: Consistent Loading States Audit (FE-006)

```
/v Audit and fix inconsistent loading state patterns across form submissions.

Search for patterns where manual loading state is used instead of LoadingButton or useForm's processing state:
1. Search for: useState.*loading.*true
2. Search for: setLoading(true)
3. Search for: await router. (fire-and-forget anti-pattern)

For each instance found:
- If it's a form submission: convert to useForm() + processing state + LoadingButton
- If it's a non-form async action: convert to LoadingButton component
- If it correctly uses onSuccess/onError callbacks: leave as-is

Focus on pages in: Settings/, Admin/, Billing/
Don't change pages that already use the correct pattern.
```

---

## Prompt 6: Infrastructure Polish (INFRA-005, INFRA-006, INFRA-007)

```
/v Apply infrastructure polish to CI pipeline.

1. INFRA-005: Make bundle size check blocking
   In .github/workflows/ci.yml, remove continue-on-error: true from the "Check bundle size" step.
   Verify scripts/check-bundle-size.sh has reasonable thresholds that current build meets.

2. INFRA-006: E2E tests should reuse build artifacts
   Add needs: [build] to the e2e-tests job.
   In the build job, upload build artifacts:
     - uses: actions/upload-artifact@v4
       with:
         name: build-assets
         path: public/build/
   In the e2e-tests job, download build artifacts instead of running npm run build:
     - uses: actions/download-artifact@v4
       with:
         name: build-assets
         path: public/build/

3. INFRA-007: Document branch protection requirements
   Add to CLAUDE.md or docs/DEPLOYMENT.md:
   "Production branch protection: require all CI status checks + 1 approval before merge to main"
```

---

## Summary Checklist

- [ ] React ErrorBoundary added and wrapping app
- [ ] CSRF verification tests for critical routes
- [ ] Rate limit test for 2FA challenge
- [ ] Webhook deliveries cursor pagination
- [ ] Loading state patterns audited and fixed
- [ ] Bundle size check blocking
- [ ] E2E tests reuse build artifacts
- [ ] Branch protection documented
