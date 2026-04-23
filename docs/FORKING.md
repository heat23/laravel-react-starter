# Forking Playbook

Operational checklist for launching a new product from this template. Run `scripts/new-saas.sh` first for the mechanical steps, then follow this checklist for the decisions it can't make for you.

---

## 1. Pre-fork decisions

Make these decisions before running the script. Write them down — you'll need them throughout.

| Decision | Notes |
|----------|-------|
| **Product name** | Used for `APP_NAME`, JSON-LD `Organization.name`, email from-name, browser tab title |
| **Domain** | Used for `APP_URL`, canonical URLs, Stripe webhook endpoint, email from-domain |
| **Stripe account** | Sub-account under your main account, or a separate Stripe account per product? (Sub-account is simpler; separate account gives cleaner isolation) |
| **Email-sending domain** | Needs SPF + DKIM records before first send. Use a subdomain (`mail.yourdomain.com`) to protect root domain reputation |
| **Price points** | Update `TEMPLATE_PRICE` env + create matching Stripe Products/Prices before launch |
| **Feature flags** | Decide which of the 12 flags to enable. Recommended starter set: `billing.enabled`, `api_tokens.enabled`, `email_verification.enabled`, `admin.enabled`, `two_factor.enabled` |

---

## 2. What the script handles (mechanical rewrites)

`scripts/new-saas.sh --target-dir <dir> --name <name> --domain <domain>` does:

1. Shallow clone of the template into `<target-dir>`
2. Find-replace `laravel-react-starter` → derived slug across `package.json`, `composer.json`, `README.md`, `.github/workflows/*.yml`
3. Substitute `{{APP_NAME}}` and `{{APP_DOMAIN}}` in `.env.example`, then copy to `.env`
4. `composer install && npm install && npm run build`
5. `php artisan key:generate`
6. Fresh git history (orphan first commit)
7. Prints this checklist

**What the script does NOT do (requires human judgment):**
- Delete marketing-only content (see Section 3)
- Set Stripe keys or webhook secrets
- Configure production environment variables
- Create Stripe products or webhook endpoints
- Enable/disable feature flags

---

## 3. Content to delete per fork

These pages exist solely to market this starter kit. Delete them before your first public deploy.

### Guide pages (6 files)

```bash
# From within your forked repo:
rm resources/js/Pages/Public/Guides/BuildVsBuyGuide.tsx
rm resources/js/Pages/Public/Guides/LaravelSaasGuide.tsx
rm resources/js/Pages/Public/Guides/SaasStarterKitComparison.tsx
rm resources/js/Pages/Public/Guides/StripeBillingGuide.tsx
rm resources/js/Pages/Public/Guides/TenancyArchitectureGuide.tsx
rm resources/js/Pages/Public/Guides/WebhookGuide.tsx
```

### Comparison directory (entire)

```bash
rm -rf resources/js/Pages/Public/Compare/
```

### Routes to remove from `routes/web.php`

Remove these lines (the compare block and matching guide lines):

```
Route::get('/compare', ...);
Route::get('/compare/laravel-jetstream', ...);
Route::get('/compare/laravel-spark', ...);
Route::get('/compare/saasykit', ...);
Route::get('/compare/wave', ...);
Route::get('/compare/shipfast', ...);
Route::get('/compare/supastarter', ...);
Route::get('/compare/larafast', ...);
Route::get('/compare/makerkit', ...);
Route::get('/compare/laravel-vs-nextjs', ...);
Route::get('/guides/building-saas-with-laravel-12', ...);
Route::get('/guides/laravel-stripe-billing-tutorial', ...);
Route::get('/guides/saas-starter-kit-comparison-2026', ...);
Route::get('/guides/cost-of-building-saas-from-scratch', ...);
Route::get('/guides/laravel-webhook-implementation', ...);
Route::get('/guides/single-tenant-vs-multi-tenant-saas', ...);
```

### SEO test datasets to trim

After removing routes, update `tests/Feature/Seo/TitleLengthTest.php` and `tests/Feature/Seo/SeoShellRendersContentTest.php` — remove the dataset entries for the deleted routes. Run `php artisan test --filter=Seo --compact` to confirm green.

### Sitemap entries

Remove the deleted URLs from `app/Http/Controllers/SeoController.php` → `buildSitemapUrls()`.

### SEO shell navigation

Update `resources/views/partials/seo-shell.blade.php` to remove the `/compare` nav link.

---

## 4. First-deploy checklist

Cross-references: `scripts/vps-setup.sh` (initial VPS provisioning) and `scripts/vps-verify.sh` (post-deploy verification).

- [ ] Run `scripts/vps-setup.sh` on a fresh Ubuntu 24.04 VPS
- [ ] Set production env vars: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://www.yourdomain.com`
- [ ] Set `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`
- [ ] Configure mail: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- [ ] Set `SENTRY_LARAVEL_DSN` (optional but recommended — free tier covers solo-op traffic)
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan optimize`
- [ ] Start queue workers via supervisor (config in `deploy/supervisor/`)
- [ ] Add domain to Cloudflare (or your DNS provider); set up SSL
- [ ] Wire synthetic monitor against `/health` (Uptime Kuma, Better Stack, or Uptimerobot) — see `deploy/MONITORING.md` when it exists
- [ ] Run `scripts/vps-verify.sh` and confirm all checks green

---

## 5. Stripe-specific setup

> **IMPORTANT — `FEATURE_BILLING_TAX` is a two-key gate.** Both `FEATURE_BILLING=true` AND `BILLING_TAX_CONFIRM_COMPLIANT=true` must be set to enable Stripe Tax. Do NOT set `BILLING_TAX_CONFIRM_COMPLIANT=true` without completing a tax compliance review with your accountant first. Incorrect tax collection creates legal liability.

### Required Stripe setup steps

1. **Create Products and Prices** in Stripe Dashboard matching `App\Enums\PlanTier` values (Free, Pro, ProTeam, Team, Enterprise). Copy the Price IDs into your `.env`.
2. **Create a webhook endpoint** in Stripe Dashboard pointing to `https://www.yourdomain.com/stripe/webhook`. Select at minimum: `customer.subscription.*`, `invoice.payment_succeeded`, `invoice.payment_failed`.
3. **Copy webhook signing secret** → `STRIPE_WEBHOOK_SECRET` in `.env`.
4. **Set Stripe keys**: `STRIPE_KEY` (publishable), `STRIPE_SECRET` (secret). Use test-mode keys until ready for live transactions.
5. **Enable billing feature flag**: `FEATURE_BILLING=true` in `.env`.
6. **Test with Stripe test cards**: `4242 4242 4242 4242` (success), `4000 0025 0000 3155` (3DS), `4000 0000 0000 9995` (decline).
7. **Run the incomplete-payment check command** after go-live: `php artisan subscriptions:check-incomplete` (send reminders at 1h/12h).
