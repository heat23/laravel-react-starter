# Sprint 4 — Polish & P3 Backlog
# Each section is a standalone /v prompt.

---

## Fix A11Y-001 + A11Y-002: Dark mode contrast + toast ARIA roles

### A11Y-001: Dashboard health score dark mode variant
**File:** `resources/js/Pages/Dashboard.tsx:60`
Change `'text-orange-500'` to `'text-orange-600 dark:text-orange-400'` in the `healthLabel` function for the 'At Risk' case.

### A11Y-002: Error toasts with role='alert'
**File:** `resources/js/hooks/useFlashToasts.ts`
When displaying error or warning flash messages, pass `{role: 'alert'}` option:
```ts
toast.error(message, { role: 'alert' });
toast.warning(message, { role: 'alert' });
// success + info keep default role='status'
```

---

## Fix DS-001 + DS-002: Design token consistency

### DS-001: Remove hardcoded hex fallbacks from ThemeProvider
**File:** `resources/js/Components/theme/ThemeProvider.tsx:57`
Remove `|| "#0f1318"` and `|| "#1D4ED8"` fallbacks. Return empty string if CSS variable is not set.

### DS-002: NPS responses semantic color tokens
**File:** `resources/js/Pages/Admin/NpsResponses/Index.tsx:47`
Replace:
- `'text-green-600 dark:text-green-400'` → `'text-success'`
- `'text-yellow-600 dark:text-yellow-400'` → `'text-warning'`
- `'text-red-600 dark:text-red-400'` → `'text-destructive'`

---

## Fix UX-001 + UX-002: Design system consistency

### UX-001: Contact page success alert
**File:** `resources/js/Pages/Contact.tsx:88`
Replace the custom `div` with hardcoded green classes with:
```tsx
<Alert variant="success" className="mb-6">
  <AlertDescription>Your message has been sent...</AlertDescription>
</Alert>
```

### UX-002: Admin notifications subject truncation
**File:** `resources/js/Pages/Admin/Notifications/Dashboard.tsx:196`
Wrap `n.subject` in the existing `TruncateWithTooltip` component with `maxWidth={300}`.

---

## Fix DEBT-003: Sitemap lastmod dates

**Finding:** `SeoController::buildSitemap()` hardcodes ISO 8601 lastmod strings.

**File:** `app/Http/Controllers/SeoController.php`

Create a config array for static page lastmod dates, keyed by URL. For dynamic pages (blog posts), continue using file modification time. For static pages, use `now()->toISOString()` as a fallback when the config date is older than 30 days (signals content may have changed).

Alternatively, read lastmod from git: `git log -1 --format="%aI" -- resources/js/Pages/Welcome.tsx` and cache the result for 24 hours.

---

## Fix TEST-001: Convert AuthenticationTest to Pest syntax

**File:** `tests/Feature/Auth/AuthenticationTest.php`

Convert all `public function test_*(): void {}` methods to Pest `it()` or `test()` top-level functions. Remove the class wrapper. Follow the pattern used in `tests/Feature/Auth/RegistrationTest.php`.

---

## Fix DOCS-001 + DOCS-002: README screenshots + API docs

### DOCS-001: README screenshots
Take screenshots of key flows (auth pages, admin dashboard, billing portal, onboarding wizard) and save to `docs/screenshots/`. Uncomment the image references in `README.md`.

### DOCS-002: Generate Scribe API docs
```bash
php artisan scribe:generate
```
Commit the generated `public/docs/` directory. Add to README quick start:
```
php artisan scribe:generate  # Generate API docs (accessible at /docs when FEATURE_API_DOCS=true)
```

---

## Fix SEO-002 + SEO-003: Welcome page structured data + meta descriptions

### SEO-002: SoftwareApplication JSON-LD on Welcome page
**File:** `resources/js/Pages/Welcome.tsx`

Add inside `<Head>`:
```tsx
<script type="application/ld+json">{JSON.stringify({
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": appName,
  "applicationCategory": "DeveloperApplication",
  "operatingSystem": "Web",
  "description": "Production-ready Laravel 12 + React SaaS starter with billing, auth, admin panel, webhooks, and feature flags.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "USD",
    "description": "One-time purchase, full source code"
  }
})}</script>
```

### SEO-003: Meta descriptions for Contact + About
**File:** `resources/js/Pages/Contact.tsx` — add in `<Head>`:
```tsx
<meta name="description" content="Get in touch with the team. We're happy to answer questions about the Laravel React Starter template." />
```

**File:** `resources/js/Pages/About.tsx` — add similar description meta tag.

---

## Fix ARCH-002: Move login streak logic to CustomerHealthService

**File:** `app/Http/Controllers/DashboardController.php:51`

1. Add `calculateLoginStreak(User $user): int` method to `app/Services/CustomerHealthService.php`
2. Move the `getLoginStreak()` implementation into `CustomerHealthService::calculateLoginStreak()`
3. Add 5-minute cache: `Cache::remember("login_streak_{$user->id}", 300, fn() => ...)`
4. Remove the `class_exists(AuditLog::class)` runtime check
5. Inject `CustomerHealthService` into `DashboardController` constructor
6. Call `$this->customerHealthService->calculateLoginStreak($user)` instead of inline calculation

---

## Fix GTM-004: Create NPS survey dispatch command

**Finding:** No automated NPS survey dispatch. The `/nps/eligible` endpoint exists but no command sends surveys.

**Create:** `app/Console/Commands/SendNpsSurveys.php`

Logic:
- Target users in `ACTIVATED` or `PAYING` lifecycle_stage
- Who have not received an NPS notification in the last 90 days (check notification_logs or database notifications table)
- Who have been in their current stage for at least 30 days
- Send `NpsSurveyNotification` (create if not exists)
- Add feature flag guard: `if (!config('features.billing.enabled') && !config('features.onboarding.enabled')) return;`

Register in `routes/console.php` or `bootstrap/app.php`:
```php
Schedule::command('nps:send-surveys')->weekly()->mondays()->at('10:00');
```

---

## Fix FUNNEL-004: Wire ExpansionNudgeNotification to PQL threshold

**Finding:** `ExpansionNudgeNotification` has no automated trigger.

**File:** `app/Listeners/SendPqlUpgradeNudge.php` (or wherever PQL threshold events are handled)

Add logic: if the resource hitting the threshold is 'seats' and threshold is 80% or 100%, dispatch `ExpansionNudgeNotification` instead of / in addition to `LimitThresholdNotification`:

```php
if ($event->resource === 'seats' && $event->threshold >= 80) {
    $event->user->notify((new ExpansionNudgeNotification())->delay(now()->addHours(2)));
}
```

---

## Fix ADMIN-004: Feature flag UI warns about route-dependent flags

**Finding:** Admin UI allows enabling route-dependent flags (billing, social_auth, etc.) that require app restart. No warning shown.

**Backend change:** `app/Http/Controllers/Admin/AdminFeatureFlagController.php`

Add to the flags response array:
```php
'is_route_dependent' => in_array($flagKey, $this->featureFlagService->getRouteDependentFlags()),
'env_value' => (bool) env('FEATURE_' . strtoupper($flagKey)),
```

**Frontend change:** `resources/js/Pages/Admin/FeatureFlags/Index.tsx` (or similar)

Show a warning badge/tooltip when `flag.is_route_dependent && !flag.env_value`:
```tsx
{flag.is_route_dependent && !flag.env_value && (
  <Badge variant="warning" title="This flag controls route registration. Enabling it requires an app restart to take effect.">
    Requires restart
  </Badge>
)}
```
