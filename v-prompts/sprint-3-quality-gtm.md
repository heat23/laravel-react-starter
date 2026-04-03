# Sprint 3 — Quality & GTM Fixes
# Each section is a standalone /v prompt for a single finding.

---

## Fix GTM-001: Document and enable GA4 Measurement Protocol by default

**Finding:** GA4_MEASUREMENT_PROTOCOL_ENABLED defaults to false. Server-side analytics (billing conversions, subscription events) require this to be active.

**Files to change:**
- `.env.example` — add GA4 Measurement Protocol to analytics section with explanation
- `scripts/init.sh` — add GA4 configuration step to launch checklist

**Changes:**

In `.env.example` analytics section:
```
# Google Analytics 4
# Get your Measurement ID from GA4 > Admin > Data Streams
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX

# GA4 Measurement Protocol (required for server-side event tracking)
# Get from GA4 > Admin > Data Streams > Measurement Protocol API Secrets
GA4_MEASUREMENT_ID=${GOOGLE_ANALYTICS_ID}
GA4_API_SECRET=your-api-secret-here
GA4_MEASUREMENT_PROTOCOL_ENABLED=true  # Enable for production server-side events
```

In `scripts/init.sh` checklist output, add:
```
[ ] Configure Google Analytics: Set GOOGLE_ANALYTICS_ID, GA4_API_SECRET, and GA4_MEASUREMENT_PROTOCOL_ENABLED=true
```

**Acceptance criteria:**
- `.env.example` documents all GA4 vars with clear setup instructions
- `scripts/init.sh` checklist output includes GA4 configuration step

---

## Fix GTM-002: Add consent-gated retargeting pixel infrastructure

**Finding:** Marketing pages have GA4 but no retargeting pixel support. Cookie consent system supports 'marketing' category but no pixel fires on consent.

**Files to change:**
- `config/services.php` — add META_PIXEL_ID and GOOGLE_ADS_ID
- `.env.example` — document new vars
- `resources/views/app.blade.php` — add consent-gated pixel load block

**Changes:**

In `config/services.php`:
```php
'meta_pixel_id' => env('META_PIXEL_ID'),
'google_ads_id' => env('GOOGLE_ADS_ID'),
```

In `.env.example`:
```
# Retargeting Pixels (optional)
META_PIXEL_ID=                    # Facebook/Meta Pixel ID
GOOGLE_ADS_ID=                    # Google Ads Conversion ID (AW-XXXXXXXXXX)
```

In `resources/views/app.blade.php`, after the GA4 block, add:
```blade
@if(config('services.meta_pixel_id') || config('services.google_ads_id'))
<script>
    // Load retargeting pixels only after marketing cookie consent
    document.addEventListener('cookieConsentGranted', function(e) {
        if (!e.detail || !e.detail.categories || !e.detail.categories.marketing) return;
        
        @if(config('services.meta_pixel_id'))
        // Meta Pixel
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ config('services.meta_pixel_id') }}');
        fbq('track', 'PageView');
        @endif
        
        @if(config('services.google_ads_id'))
        // Google Ads remarketing
        var gadsScript = document.createElement('script');
        gadsScript.async = true;
        gadsScript.src = 'https://www.googletagmanager.com/gtag/js?id={{ config('services.google_ads_id') }}';
        document.head.appendChild(gadsScript);
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google_ads_id') }}');
        @endif
    });
</script>
@endif
```

Verify that `CookieConsent.tsx` dispatches a `cookieConsentGranted` CustomEvent with `{categories}` detail when the user accepts. Wire the event dispatch in `CookieConsent.tsx` if not already present.

**Acceptance criteria:**
- Pixel script tag not present on page load before consent
- After marketing consent, pixel fires a PageView event
- `META_PIXEL_ID` and `GOOGLE_ADS_ID` not set = no pixel blocks rendered

---

## Fix FUNNEL-002: Verify/add last_active_at column to users table

**Finding:** `TrackLastActivity` middleware writes to `last_active_at` but the column may not exist in migrations.

**Verification step first:** Run `php artisan tinker --execute "Schema::hasColumn('users', 'last_active_at') ? 'exists' : 'missing'"`

**If missing, add migration:**
```bash
php artisan make:migration add_last_active_at_to_users_table
```

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->timestamp('last_active_at')->nullable()->after('remember_token');
        $table->index('last_active_at'); // for re-engagement query performance
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropIndex(['last_active_at']);
        $table->dropColumn('last_active_at');
    });
}
```

Also add to User model casts:
```php
'last_active_at' => 'datetime',
```

**Acceptance criteria:**
- `Schema::hasColumn('users', 'last_active_at')` returns true
- `php artisan test --filter TrackLastActivity` passes
- `php artisan test --filter SendReEngagementEmails` passes

---

## Fix ADMIN-002 + ADMIN-003: Fix admin user detail unbounded queries + billing cache invalidation

**Finding ADMIN-002:** `AdminUsersController::show()` loads all audit logs without row limit.
**Finding ADMIN-003:** Subscription webhook handlers don't invalidate billing stats cache.

### ADMIN-002 Fix

**File:** `app/Http/Controllers/Admin/AdminUsersController.php`

Add `->limit(50)` to audit log query and `->limit(20)` to stage history query in `show()` method. Add a note in the response that "showing recent X of total Y" by also fetching total count.

### ADMIN-003 Fix

**File:** `app/Http/Controllers/Billing/StripeWebhookController.php`

In `handleCustomerSubscriptionCreated()`, `handleCustomerSubscriptionDeleted()`, and `handleCustomerSubscriptionUpdated()` (if it exists), add after the main subscription processing:

```php
Cache::forget(AdminCacheKey::BILLING_STATS->value);
Cache::forget(AdminCacheKey::BILLING_TIER_DIST->value);
```

**Acceptance criteria:**
- `php artisan test --filter AdminUsersController` passes
- Admin user detail page loads in < 8 queries even for users with 10,000+ audit entries
- `php artisan test --filter StripeWebhookController` passes
- Billing stats cache key is deleted after subscription webhook

---

## Fix SEO-001: Add canonical link + og:url to Welcome and Pricing pages

**Finding:** Both high-value SEO pages missing canonical link and og:url. Search engines may index query-parameter variants.

**Backend changes:**

In `WelcomeController::__invoke()`, add to Inertia props:
```php
'canonicalUrl' => url('/'),
```

In `PricingController::__invoke()`, add to Inertia props:
```php
'canonicalUrl' => url('/pricing'),
```

**Frontend changes:**

In `resources/js/Pages/Welcome.tsx`, in the `<Head>` component, add:
```tsx
<link rel="canonical" href={canonicalUrl} />
<meta property="og:url" content={canonicalUrl} />
```

In `resources/js/Pages/Pricing.tsx`, same addition.

Add `canonicalUrl: string` to the TypeScript props type for both pages.

**Acceptance criteria:**
- `curl -s http://localhost:8000/ | grep canonical` returns the correct URL
- `curl -s http://localhost:8000/pricing | grep canonical` returns the pricing URL
- `curl -s http://localhost:8000/pricing?plan=pro | grep og:url` returns /pricing without the query param

---

## Fix LAUNCH-003 + LAUNCH-004: SESSION_SECURE_COOKIE + E2E baseURL

**Finding LAUNCH-003:** SESSION_SECURE_COOKIE not in .env.example defaults.
**Finding LAUNCH-004:** E2E tests hardcode localhost:8000.

### LAUNCH-003 Fix

In `.env.example` session section, add:
```
SESSION_SECURE_COOKIE=true        # Set to false only for non-HTTPS local dev
```

In `AppServiceProvider::boot()`:
```php
if (app()->isProduction() && !config('session.secure')) {
    Log::warning('PRODUCTION WARNING: SESSION_SECURE_COOKIE should be true in production to prevent cookie theft over HTTP.');
}
```

### LAUNCH-004 Fix

In `playwright.config.ts`:
```typescript
export default defineConfig({
  use: {
    baseURL: process.env.APP_URL || 'http://localhost:8000',
    ...
  },
  webServer: {
    url: process.env.APP_URL || 'http://localhost:8000',
    ...
  },
});
```

**Acceptance criteria:**
- `npm run test:e2e` passes with `APP_URL=http://localhost:8000`
- `APP_URL=http://localhost:9000 npm run test:e2e` uses port 9000
- `.env.example` includes SESSION_SECURE_COOKIE=true

---

## Fix FUNNEL-003: Add billing feature flag guard to win-back commands

**Finding:** `SendWinBackEmails` and other billing-dependent commands don't guard against billing being disabled.

**Files:** All billing-dependent console commands:
- `app/Console/Commands/SendWinBackEmails.php`
- `app/Console/Commands/SendDunningReminders.php`
- `app/Console/Commands/SendTrialEndingReminders.php`
- `app/Console/Commands/SendTrialNudges.php` (trial-specific, but billing-adjacent)

**Change for each `handle()` method**, at the top:
```php
public function handle(): int
{
    if (!config('features.billing.enabled')) {
        $this->info('Billing feature is disabled. Skipping.');
        return Command::SUCCESS;
    }
    // ... existing logic
}
```

**Acceptance criteria:**
- Commands exit cleanly when `FEATURE_BILLING=false`
- Existing tests still pass (they test with billing enabled by default in phpunit.xml)
