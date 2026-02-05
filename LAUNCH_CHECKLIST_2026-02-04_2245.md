# LAUNCH_CHECKLIST: Laravel React Starter
generated: 2026-02-04
filename: LAUNCH_CHECKLIST_2026-02-04_2245.md
target_launch: TBD
status: NO-GO
stack: react

## EXECUTIVE_SUMMARY

### Launch Readiness
| Category | Status | Blockers |
|----------|--------|----------|
| Environment | ⚠️ warn | 2 |
| Payments | ✅ N/A | 0 |
| Database | ✅ ok | 0 |
| Assets | ✅ ok | 0 |
| SSL/Domain | ⚠️ warn | 1 |
| Security | ✅ ok | 0 |
| Testing | ✅ ok | 0 |
| Legal | ❌ fail | 2 |
| Documentation | ❌ fail | 1 |
| SEO | ❌ fail | 2 |

**blockers_total: 8**
**decision: NO-GO**

### Self-Sufficiency
| Area | Status | Weekly Time |
|------|--------|-------------|
| Backups | ❌ missing | 30 min manual |
| Error Monitoring | ❌ missing | 60 min manual |
| Uptime Monitoring | ❌ missing | 15 min manual |
| Dependency Updates | ⚠️ partial | 20 min |
| CI/CD | ✅ configured | 5 min |
| **TOTAL** | | **130 min** |

**target:** < 30 min/week
**current:** ~130 min/week (needs automation)

---

## BLOCKERS (Must Fix Before Launch)

### BLOCK-001: APP_ENV set to local
**category:** environment
**severity:** critical
**current:** `APP_ENV=local`
**required:** `APP_ENV=production`
**fix:**
```bash
# In .env for production:
APP_ENV=production
```

### BLOCK-002: APP_DEBUG enabled
**category:** environment
**severity:** critical
**current:** `APP_DEBUG=true`
**required:** `APP_DEBUG=false`
**fix:**
```bash
# In .env for production:
APP_DEBUG=false
```

### BLOCK-003: Missing Terms of Service
**category:** legal
**severity:** critical
**impact:** Legal liability, app store rejection, GDPR non-compliance
**fix:**
```bash
# Option 1: Run vibe-docs skill
/vibe-docs

# Option 2: Create manually at:
# - resources/js/Pages/Legal/Terms.tsx
# - Add route in routes/web.php
```

### BLOCK-004: Missing Privacy Policy
**category:** legal
**severity:** critical
**impact:** GDPR/CCPA violation, legal liability
**fix:**
```bash
# Option 1: Run vibe-docs skill
/vibe-docs

# Option 2: Create manually at:
# - resources/js/Pages/Legal/Privacy.tsx
# - Add route in routes/web.php
```

### BLOCK-005: Missing robots.txt
**category:** SEO
**severity:** critical
**impact:** Search engines may not crawl correctly
**fix:**
```bash
cat > public/robots.txt << 'EOF'
User-agent: *
Allow: /
Disallow: /api/
Disallow: /profile
Disallow: /dashboard

Sitemap: https://yourdomain.com/sitemap.xml
EOF
```

### BLOCK-006: Missing sitemap.xml
**category:** SEO
**severity:** critical
**impact:** Search engine indexing issues
**fix:**
```bash
# Install sitemap generator
composer require spatie/laravel-sitemap

# Generate sitemap
php artisan sitemap:generate

# Or create minimal static sitemap:
cat > public/sitemap.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://yourdomain.com/</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://yourdomain.com/login</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>https://yourdomain.com/register</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
</urlset>
EOF
```

### BLOCK-007: Missing Documentation
**category:** documentation
**severity:** critical
**impact:** User onboarding friction, support burden
**fix:**
```bash
# Run vibe-docs skill to generate:
/vibe-docs

# This will create:
# - docs/quick-start.md
# - docs/api/ (if applicable)
# - docs/faq.md
```

### BLOCK-008: SSL/Domain Not Verified
**category:** SSL
**severity:** critical
**impact:** Cannot launch without HTTPS
**verification_needed:**
- [ ] Domain purchased and DNS configured
- [ ] SSL certificate installed (Let's Encrypt recommended)
- [ ] HTTP→HTTPS redirect configured
- [ ] APP_URL in .env set to https://yourdomain.com

---

## WARNINGS (Should Fix)

### WARN-001: Using SQLite database
**category:** database
**impact:** Not suitable for production with multiple users
**recommendation:** Switch to MySQL/PostgreSQL for production
**fix:**
```bash
# In .env for production:
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### WARN-002: Mail configured for local (Mailpit)
**category:** environment
**impact:** Emails won't send in production
**fix:**
```bash
# Configure real SMTP in .env:
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org  # or your provider
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### WARN-003: Missing OpenGraph tags
**category:** SEO
**impact:** Poor social media sharing appearance
**fix:**
```php
// In resources/views/app.blade.php <head>:
<meta property="og:title" content="{{ $page['props']['title'] ?? config('app.name') }}">
<meta property="og:description" content="Your app description">
<meta property="og:image" content="{{ asset('images/og-image.png') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta name="twitter:card" content="summary_large_image">
```

### WARN-004: No security headers middleware
**category:** security
**impact:** Lower security score, potential vulnerabilities
**fix:**
```php
// Create app/Http/Middleware/SecurityHeaders.php
// Add headers: X-Frame-Options, X-Content-Type-Options, etc.
// Or use: composer require bepsvpt/secure-headers
```

### WARN-005: Dependabot not configured
**category:** automation
**impact:** Manual dependency monitoring needed
**fix:**
```bash
mkdir -p .github
cat > .github/dependabot.yml << 'EOF'
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 5
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 5
EOF
```

---

## PASSING CHECKS ✅

### Environment
- [x] APP_KEY set and valid
- [x] Feature flags system in place

### Database
- [x] All 6 migrations run successfully
- [x] Migration status verified

### Assets
- [x] Build passes without errors
- [x] SSR build successful
- [x] Bundle size reasonable (~210KB gzipped total)

### Security
- [x] Rate limiting on auth routes (registration, password reset)
- [x] CSRF protection enabled (Laravel default)
- [x] composer audit: No vulnerabilities
- [x] npm audit: No vulnerabilities
- [x] UserPolicy for explicit authorization
- [x] Atomic social disconnect with transaction locking

### Testing
- [x] 29 tests passing
- [x] 59 assertions
- [x] Test coverage includes: auth, profile, rate limiting, tokens

### CI/CD
- [x] GitHub Actions workflow configured (ci.yml)
- [x] PHP tests with coverage
- [x] JS tests
- [x] Build verification
- [x] Security audits in CI

---

## AUTOMATION_SETUP

### AUTO-001: Database Backups
**status:** ❌ missing
**time_saved:** 25 min/week
**setup:**
```bash
# 1. Install backup package
composer require spatie/laravel-backup

# 2. Publish config
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# 3. Configure config/backup.php with S3/R2 disk

# 4. Add to app/Console/Kernel.php (or routes/console.php in Laravel 11+):
Schedule::command('backup:run')->daily()->at('02:00');
Schedule::command('backup:clean')->daily()->at('03:00');

# 5. For cloud storage, configure in config/filesystems.php:
# - AWS S3 or Cloudflare R2 (S3-compatible, cheaper)
```

### AUTO-002: Error Monitoring (Sentry)
**status:** ❌ missing (config placeholders exist)
**time_saved:** 55 min/week
**setup:**
```bash
# 1. Sign up at sentry.io (free tier: 5K events/month)

# 2. Install package
composer require sentry/sentry-laravel

# 3. Publish and configure
php artisan sentry:publish --dsn=YOUR_DSN_HERE

# 4. Add to .env:
SENTRY_LARAVEL_DSN=https://xxx@xxx.ingest.sentry.io/xxx
VITE_SENTRY_DSN=https://xxx@xxx.ingest.sentry.io/xxx

# 5. Configure alerts in Sentry dashboard:
#    - Email on first occurrence of new errors
#    - Daily digest of error counts
```

### AUTO-003: Uptime Monitoring
**status:** ❌ missing
**time_saved:** 10 min/week
**setup:**
```bash
# 1. Sign up at uptimerobot.com (free: 50 monitors)

# 2. Add monitors:
#    - HTTP(s) monitor: https://yourdomain.com
#    - Keyword monitor: Check for expected content
#    - SSL expiry alert

# 3. Configure alerts:
#    - Email on downtime
#    - Slack/Discord webhook (optional)

# Alternative: betteruptime.com, pingdom.com
```

### AUTO-004: Dependency Updates (Dependabot)
**status:** ⚠️ partial (auto-merge workflow exists, dependabot.yml missing)
**time_saved:** 100 min/month
**setup:**
```yaml
# Create .github/dependabot.yml:
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 5
    labels:
      - "dependencies"
      - "php"

  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 5
    labels:
      - "dependencies"
      - "javascript"
```

---

## ROLLBACK_PLAN

```bash
# If something goes wrong after deployment:

# 1. Revert to previous version
git log --oneline -5  # Find the previous good commit
git checkout <previous-commit-hash>

# 2. Rollback migrations if needed
php artisan migrate:rollback --step=1

# 3. Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Rebuild assets
npm run build

# 5. Restart queue workers
php artisan queue:restart

# 6. Verify rollback
php artisan test --filter=SmokeTest
```

---

## LAUNCH_PROCEDURE

### T-7 (One Week Before)
- [ ] Fix all BLOCKERS listed above
- [ ] Address WARNINGS (especially WARN-001, WARN-002)
- [ ] Set up production environment (hosting, database)
- [ ] Configure DNS and SSL
- [ ] Run `/vibe-docs` to generate documentation

### T-1 (Day Before)
- [ ] Final code freeze
- [ ] Run full test suite: `php artisan test`
- [ ] Production build: `npm run build`
- [ ] Backup current state
- [ ] Notify stakeholders
- [ ] Prepare rollback procedure

### T-0 (Launch Day)
```bash
# 1. Final verification
php artisan test
npm run build

# 2. Deploy to production
git push production main  # Or your deployment method

# 3. Run migrations
php artisan migrate --force

# 4. Clear and optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Smoke test critical paths
# - [ ] Homepage loads
# - [ ] Registration works
# - [ ] Login works
# - [ ] Dashboard accessible
# - [ ] Profile update works

# 6. Verify monitoring
# - [ ] Check Sentry for test error
# - [ ] Check uptime monitor is green
```

### T+1 (Next Day)
- [ ] Review Sentry for any errors
- [ ] Check server response times
- [ ] Review user feedback/support tickets
- [ ] Monitor database performance

### T+7 (Week After)
- [ ] Compile metrics report
- [ ] Address any recurring issues
- [ ] Plan next iteration
- [ ] Set up automation (backups, monitoring)

---

## MAINTENANCE_RUNBOOK

### Weekly Tasks (target: 15 min)

**Monday (5 min):**
- [ ] Check Sentry dashboard for new errors
- [ ] Review uptime report
- [ ] Quick scan of server logs

**Wednesday (5 min):**
- [ ] Review Dependabot PRs
- [ ] Merge safe dependency updates

**Friday (5 min):**
- [ ] Check support queue
- [ ] Deploy any pending fixes
- [ ] Verify backup ran successfully

### Monthly Tasks (30 min)
- [ ] Test backup restore procedure
- [ ] Review hosting costs
- [ ] Update FAQ with common questions
- [ ] Security audit (run `composer audit && npm audit`)
- [ ] Performance review (check Lighthouse scores)

### Quarterly Tasks (60 min)
- [ ] Major dependency updates
- [ ] Full security review
- [ ] Performance optimization
- [ ] Documentation update
- [ ] User feedback review

---

## COST_BREAKDOWN (Estimated)

| Service | Purpose | Monthly Cost |
|---------|---------|--------------|
| Sentry | Error tracking | $0 (5K events free) |
| UptimeRobot | Uptime monitoring | $0 (50 monitors free) |
| Cloudflare R2 | Backups | ~$0.50 (10GB) |
| GitHub Actions | CI/CD | $0 (2K min/month free) |
| **TOTAL** | | **~$0.50/month** |

*Note: Hosting costs not included (varies by provider)*

---

## NEXT_STEPS

### Immediate (Before Launch)
1. ❌ Fix all 8 BLOCKERS
2. ⚠️ Address environment warnings (production settings)
3. 📄 Run `/vibe-docs` to generate documentation
4. 🔒 Configure production environment

### Post-Launch (Week 1)
1. Set up Sentry error monitoring
2. Configure uptime monitoring
3. Set up automated backups
4. Create Dependabot config

### Post-Launch (Month 1)
1. Review and optimize based on real usage
2. Gather user feedback
3. Plan feature roadmap

---

## VERIFICATION CHECKLIST

### Pre-Launch
- [ ] `php artisan test` - All 29 tests pass
- [ ] `npm run build` - No errors
- [ ] `composer audit` - No vulnerabilities
- [ ] `npm audit` - No vulnerabilities
- [ ] All BLOCKERS resolved
- [ ] Production environment configured
- [ ] SSL certificate installed
- [ ] Legal pages published

### Post-Launch
- [ ] Trigger test error → appears in Sentry
- [ ] Backup runs → file appears in storage
- [ ] Uptime monitor → receiving heartbeats
- [ ] User can register, login, and use app
