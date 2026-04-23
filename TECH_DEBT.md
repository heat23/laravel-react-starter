# Tech Debt Audit — Laravel React Starter

**Last audit:** 2026-04-22
**This update:** 2026-04-23 (re-audit after significant remediation work)
**Audited for:** Sole-operator use as a base template for multiple SaaS products
**Optimization lenses:** (a) Maintainability over 6–12 months, (b) Pre-launch hardening for paid customers, (c) Speed to first ship when forking the template

## TL;DR — what changed since last audit

**Large net positive.** Most of the high-impact Code and Architecture items are closed, the repo is clean of committed audit artifacts, and two new docs (`SYSTEM_DESIGN_SOLO_OPERATOR.md`, `architecture-removal-plan.md`) show an active pruning discipline. The lifecycle stack was dramatically simplified — the old scoring/analytics/UTM surfaces were removed in favor of a lean audit-only trail, which retires several Phase-2 and Phase-3 items entirely.

The remaining debt clusters in **three specific places**, in decreasing priority:

1. **Forking / pre-launch scaffolding still missing.** No `docs/FORKING.md`, no `scripts/new-saas.sh`, no `docker-compose.dev.yml`, no synthetic production monitor, and **E2E still covers only auth** (billing, webhooks, 2FA have no end-to-end protection).
2. **PHPStan baseline is still 70 errors across 421 lines.** Slight reduction (83 → 70), but the class of errors is the same — Cashier model dynamic property access, Carbon casts missing on date columns, unresolvable Collection::map callbacks. Installing `barryvdh/laravel-ide-helper` would close the majority in one sitting.
3. **Documentation sprawl got worse, not better.** Four new docs added (`SYSTEM_DESIGN_SOLO_OPERATOR.md`, `architecture-removal-plan.md`, `architecture-review-sole-operator.md`, `FEATURE_FLAG_AUDIT.md`) on top of the pre-existing five-way overlap in AI guidance. Great content, but still no single-entry-point `docs/README.md` indexing it.

Everything in the previous "Code debt" section — oversized services, 571-line controllers, marketing-page sprawl — is either done or materially smaller.

## Changelog: what moved since the 2026-04-22 audit

### ✅ Closed / done

| Item | Evidence | Notes |
|------|----------|-------|
| C2 — Split `SubscriptionController` (was 571 lines, 11 actions) | `app/Http/Controllers/Billing/` now has `SubscriptionCheckoutController`, `SubscriptionLifecycleController`, `PaymentMethodController`, `RetentionController` (+ `BillingController`, `PricingController`, `StripeWebhookController`) | Commit `b150cf0` split four ways; `LoadBillingContext` middleware extracted; shared `HandlesBillingErrors` trait introduced |
| C3 — Split `FeatureFlagService` (was 496 lines, 12 methods) | Now `FeatureFlagService` 200 lines + `FeatureFlagOverrideStore` 163 lines + `FeatureFlagValidator` 43 lines | Commit `e476fc8`; `CacheInvalidationManager` wired exclusively |
| O1 — 18 audit/review files tracked in git | `git ls-files \| grep -E "(audit-\|BILLING_REVIEWED_\|...)"` returns 0 | Cleaned via multiple `chore` commits |
| A3 — `resources/js/Pages/` reorganization | Directory now has `App/` and `Public/` namespaces; page count 130 → 96 (34 deleted) | Commit `4926063` |
| (was on the "not debt" list) PlanTier enum | `App\Enums\PlanTier` (Free, Pro, ProTeam, Team, Enterprise) now single source of truth for billing | Commit `873d93d` |
| Webhook architecture | `WebhookProvider` interface + `StripeEventMap` dispatcher + per-event handlers in `app/Webhooks/Stripe/Handlers/` | Commit `ec80a52` |
| Admin routes | Split out to `routes/admin.php` (15 lines), grouped via `ListsAdminResources` trait + `AdminListRequest` base class | Commit `68cadf8` |
| Admin auth / 2FA gating | Hardened authorization and 2FA session/verification gates | Commit `4a4f46e` |
| Lifecycle/scoring/analytics simplification | `EngagementScoringService`, `LeadScoringService`, `CustomerHealthService`, `AnalyticsGateway`, UTM middleware, GA4 forwarding — all removed per CLAUDE.md. Only `lifecycle:send-welcome-sequence` remains | Retires items T3 (trial Carbon bugs in dunning/nudge commands) — those commands no longer exist |
| SEO hardening | JSON-LD numeric prices, @id + cross-refs, ImageObject logos, SEO Blade shell, title-length tests | Commit `d020e1f` |
| IndexNow | New 12th feature flag `indexnow.enabled` for instant-indexing pings | Commit `a55e23c` |
| Doc4 — ADRs | 4 → 5 ADRs (added `0005-admin-vs-super-admin-delegation.md`) | Continuing, on track |
| T6 — `infection` mutation testing | Actually was always in `composer.json` (`^0.32.4`) — I misreported in the prior audit. **Not a debt item.** | Self-correction |

### 🟡 Partially done

| Item | Before | After | Remaining work |
|------|--------|-------|----------------|
| T2 — PHPStan baseline | 83 errors / 499 lines | 70 errors / 421 lines | 84% of errors remain; dominant patterns unchanged (Cashier dynamic properties, Carbon/string, unresolvable Collection::map) |
| O2 — Stale report files in root | 285 total files in root | 47 total; **3 stragglers** share a single session UUID (`2597c859…`) | 3 files to delete: `AGENT_REVIEW_…md`, `PRE_FLIGHT_REPORT_…md`, `VERIFY_DONE_REPORT_…md` |
| C1 — `BillingService` size | 486 lines, 16 methods | 501 lines (retention coupon moved in from controller) | Retention consolidation is a net-positive; class is still large but now better-aligned to a single "mutation orchestrator" role. Defer split; revisit only if it grows past ~600 |
| C4 — `AdminBillingStatsService` size | 513 lines, 9 methods | 514 lines, 10 methods | Unchanged in size; still a candidate for reader/paginator split |
| Doc2 — Forking playbook | Nothing | `docs/SYSTEM_DESIGN_SOLO_OPERATOR.md` exists + `architecture-removal-plan.md` exists | These are *architecture* docs. Still need an operational `docs/FORKING.md` checklist + a `scripts/new-saas.sh` that does the mechanical rewrites |
| A1 — `web.php` route count | 55 routes | **81 routes, 239 lines** | Got worse; route-file splitting now past the pain threshold |

### ❌ Still open (unchanged from prior audit)

| Item | Still true because |
|------|---------------------|
| T1 — E2E coverage only auth | `tests/e2e/` specs: `auth.spec.ts`, `pages/login.spec.ts`, `pages/register.spec.ts`, `pages/forgot-password.spec.ts`, `pages/welcome.spec.ts`. **No billing, webhook, or 2FA E2E.** |
| I1 — No synthetic production monitor | `deploy/MONITORING.md` absent; no Uptime Kuma / Better Stack / Uptimerobot wiring documented |
| I2 — No `docker-compose.dev.yml` | Still no local-dev compose file; each fork needs manual MySQL + Redis + Mailpit setup |
| D1 — `phpunit/phpunit` ^12.0 in `require-dev` | Still present; Pest already wraps it |
| D2 — `axios` ^1.11 in `devDependencies` | Still present; unclear if actually imported |
| Doc1 — AI-workflow doc sprawl | Got worse. `docs/` now includes `AI_DEVELOPMENT_SAFEGUARDS.md`, `AI_PROMPT_TEMPLATES.md`, `IMPLEMENTATION_GUARDRAILS.md`, `PLANNING_CHECKLIST.md`, `PROACTIVE_SAFEGUARDS_SUMMARY.md`, `SYSTEM_DESIGN_SOLO_OPERATOR.md`, `architecture-removal-plan.md`, `architecture-review-sole-operator.md`, `FEATURE_FLAG_AUDIT.md`, `TEST_PHASE1_COMPLETION.md` — 10+ overlapping files, no index |
| T4 — SEO sitemap still manually maintained | `SeoController::buildSitemapUrls()` still a hardcoded list; still three parallel test files carrying the route list by hand |
| C5 — Giant marketing-guide pages | `BuildVsBuyGuide.tsx` 1,130 lines, `SaasStarterKitComparison.tsx` 1,104, `NextjsSaas.tsx` 995, `TenancyArchitectureGuide.tsx` 961, `Pricing.tsx` 940, `LaravelSaasGuide.tsx` 886, `Welcome.tsx` 877, `StripeBillingGuide.tsx` 827, `WebhookGuide.tsx` 807. Nine files >800 lines |

### 🆕 Debt surfaced by the recent changes

| Item | Location | Why it's new |
|------|----------|--------------|
| N1 — `web.php` grew 47% | `routes/web.php` (239 lines, 81 route declarations) | Went from "approaching" to "past" the threshold for splitting into domain-scoped route files. Previous recommendation to split into `routes/marketing.php`, `routes/public.php` etc. is now more urgent |
| N2 — `docs/` sprawl worsened | See Doc1 row above | Each remediation round added an architecture doc without retiring older ones |
| N3 — 3 straggler report files | Repo root | Single-session leak of the AGENT_REVIEW / PRE_FLIGHT_REPORT / VERIFY_DONE_REPORT tuple, all with UUID `2597c859-8128-4dc6-b536-575f0ea606f9` |

## Scoring methodology (unchanged)

- **Impact (1–5):** how much this slows you down or compounds over time
- **Risk (1–5):** what happens to revenue, security, uptime, or data if you don't fix it
- **Effort (1–5):** 1 ≈ minutes, 2 ≈ hours, 3 ≈ one day, 4 ≈ several days, 5 ≈ a week+

**Priority = (Impact + Risk) × (6 − Effort).** Higher is more urgent.

## Remaining debt inventory (only open items)

### Operational / hygiene

| # | Item | Impact | Risk | Effort | Priority |
|---|------|--------|------|--------|----------|
| O2a | Delete 3 straggler report files in root | 1 | 1 | 1 | 10 |
| D1 | Remove `phpunit/phpunit` from `require-dev` (no direct imports found) | 1 | 1 | 1 | 10 |
| D2 | Resolve `axios` status — confirm usage, remove or promote to `dependencies` | 1 | 2 | 1 | 15 |
| D3 | Ensure CI `security` job fails build on moderate+ audit findings | 2 | 3 | 1 | 25 |
| O4 | Verify `.env.example` covers every `env()` reference in `config/**` | 2 | 3 | 2 | 20 |

### Infrastructure / pre-launch hardening

| # | Item | Impact | Risk | Effort | Priority |
|---|------|--------|------|--------|----------|
| **Doc2** | **Write `docs/FORKING.md` + `scripts/new-saas.sh`** (the `SYSTEM_DESIGN_SOLO_OPERATOR.md` is great but is architecture commentary, not an ops checklist) | **5** | **4** | **2** | **36** |
| I1 | Wire synthetic monitor against `/health` (Uptime Kuma / Better Stack / Uptimerobot) and document in `deploy/MONITORING.md` | 3 | 4 | 2 | 28 |
| I2 | Write `docker-compose.dev.yml` (MySQL 8 + Redis + Mailpit) for per-fork onboarding | 3 | 2 | 2 | 20 |
| I3 | Document required branch-protection checks (`php-tests`, `js-tests`, `build`, `code-quality`, `e2e-tests`) in a `docs/OPS.md` | 2 | 3 | 1 | 25 |
| I4 | VPS runbook for multi-product hosting — what's shared, what's per-product | 2 | 3 | 2 | 20 |
| I5 | Cache Playwright Chromium install in CI (saves ~60s/run) | 1 | 1 | 1 | 10 |

### Test coverage / PHPStan

| # | Item | Impact | Risk | Effort | Priority |
|---|------|--------|------|--------|----------|
| T2 | Install `barryvdh/laravel-ide-helper`, regenerate stubs, fix Cashier PHPDoc — drops the 70-error baseline to ~15 | 4 | 3 | 2 | 28 |
| T1 | Playwright E2E for billing (checkout, cancel, swap), webhook receipt (`invoice.payment_succeeded`), 2FA enrollment + challenge | 4 | 4 | 4 | 16 |
| T4 | SEO invariant auto-discovery — derive the public-route list from `RouteServiceProvider` and reuse across `JsonLdValidityTest`, `SeoShellRendersContentTest`, `TitleLengthTest`, and `SeoController::buildSitemapUrls()` | 3 | 3 | 3 | 18 |
| T5/Doc3 | Link the 1 contract test (`FeatureFlagContractTest`) to a documenting ADR. Minor — the contract suite is tiny now | 1 | 2 | 1 | 15 |

### Code shape (continuous — address when touching)

| # | Item | Impact | Risk | Effort | Priority |
|---|------|--------|------|--------|----------|
| C4 | Split `AdminBillingStatsService` (514 lines) into `AdminBillingStatsReader` + `AdminBillingSubscriptionPaginator` | 2 | 2 | 3 | 12 |
| C5 | Extract marketing-guide pages (>800 lines each) into section components or move to MDX — only worth it if these guides get reused across multiple SaaS forks; otherwise delete them per-fork | 2 | 1 | 3 | 9 |
| C6 | Controller-business-logic → service migration pass (74 controllers vs. 20 services) | 2 | 2 | 4 | 8 |
| N1 | Split `web.php` (81 routes, 239 lines) into domain-scoped route files (`routes/marketing.php`, `routes/public.php`, `routes/app.php`) | 3 | 1 | 2 | 16 |
| A4 | Cashier v16 → v17 changelog watch (tight coupling via extended `Subscription` model) | 2 | 3 | 1 | 25 |

### Documentation

| # | Item | Impact | Risk | Effort | Priority |
|---|------|--------|------|--------|----------|
| Doc1 | Consolidate 10+ overlapping AI/architecture docs into a single `docs/README.md` index + retire duplicates | 3 | 1 | 2 | 16 |
| Doc4 | Backfill ADRs for: lifecycle-stack removal, PlanTier enum adoption, WebhookProvider pattern, Pages App/Public split. Each is a non-trivial decision; none yet captured as ADRs | 2 | 2 | 3 | 12 |

## Updated phased remediation plan

### Phase 0 — Under 30 minutes

```bash
# O2a: remove the 3 straggler report files
rm AGENT_REVIEW_2597c859*.md PRE_FLIGHT_REPORT_2597c859*.md VERIFY_DONE_REPORT_2597c859*.md

# D1: remove phpunit (after confirming no direct `use PHPUnit\` in tests/)
grep -rE "use PHPUnit\\\\" tests/ && echo "STOP — has direct imports" || composer remove --dev phpunit/phpunit

# D2: confirm axios status
grep -rE "from ['\"]axios['\"]|require\(['\"]axios" resources/js/
# If zero matches: npm uninstall axios
# Otherwise: move axios to "dependencies" in package.json

# I3: quick write-up
cat > docs/OPS.md << 'EOF'
# Ops — Required CI Checks
These jobs must be green on every PR to main:
- php-tests (Pest, parallel, PCOV coverage)
- js-tests (Vitest)
- build (Vite production build)
- code-quality (Pint + PHPStan)
- e2e-tests (Playwright)
Configure GitHub branch protection accordingly.
EOF
```

### Phase 1 — The forking playbook (1–2 days)

This is the single highest-leverage item still open (priority 36) and is uniquely valuable for the sole-operator, multi-product use case.

**Deliverable 1: `docs/FORKING.md`** — an operational checklist, not architecture commentary. Contents:

1. Pre-fork decisions to make: product name, domain, stripe account vs. sub-account, email-sending domain, target price points, which of the 12 feature flags to enable out of the box.
2. Mechanical rewrites the script handles: `APP_NAME`, `config/app.php` defaults, SEO defaults (`title`, OG image, Organization JSON-LD name, canonical URL), email templates, `package.json` name, git remote reset.
3. Content to delete per fork: the 9 marketing-guide pages (`BuildVsBuyGuide`, `SaasStarterKitComparison`, `NextjsSaas`, `TenancyArchitectureGuide`, `LaravelSaasGuide`, `StripeBillingGuide`, `WebhookGuide`, plus comparisons under `Pages/Public/Compare/`) that make sense only in the starter's own marketing context.
4. First-deploy checklist: cross-link `scripts/vps-setup.sh` + `scripts/vps-verify.sh`; set monitoring; add domain to CloudFlare.
5. Stripe-specific setup: test products, webhook endpoints, `STRIPE_WEBHOOK_SECRET`, tax setting decision (don't enable `FEATURE_BILLING_TAX` without compliance review).

**Deliverable 2: `scripts/new-saas.sh`** — takes `--target-dir`, `--name`, `--domain` arguments. Does:

1. `git clone --depth=1` the template into target.
2. Find-replace: `laravel-react-starter` → new slug across package.json, composer.json, README, CI configs.
3. Delete marketing-guide pages enumerated in `FORKING.md`.
4. Regenerate `.env` from `.env.example` with the new `APP_NAME`, `APP_URL`.
5. `composer install && npm install && npm run build`.
6. `git init` fresh in target dir (orphan first commit).
7. Print the residual checklist from `FORKING.md` that requires human decisions.

### Phase 2 — Pre-launch hardening (1–2 weeks)

Do these before the next paid customer subscription mutation.

1. **T2 — PHPStan baseline reduction.** `composer require --dev barryvdh/laravel-ide-helper`, then `php artisan ide-helper:models -W`, `php artisan ide-helper:generate`. Re-run `vendor/bin/phpstan analyse` and rebuild the baseline. Target: 70 → ≤15 suppressed errors. This is a single afternoon of work and materially improves refactor safety.
2. **I1 — Synthetic monitor.** Add Uptime Kuma (self-hosted, $0) or Better Stack ($25/mo) pinging `/health` every 60s with a token. Write `deploy/MONITORING.md` with the token rotation runbook. Wire alerting to your email/phone.
3. **T1 — Billing/webhook/2FA E2E.** Minimum four Playwright specs:
   - `billing-checkout.spec.ts` — free user → `/pricing` → Stripe test card `4242 4242 4242 4242` → land on dashboard with `pro` tier.
   - `billing-cancel.spec.ts` — pro user → cancel → confirm retains access until period end; `subscriptions.stripe_status` is `canceled`.
   - `webhook-stripe.spec.ts` — use Stripe CLI to trigger `invoice.payment_succeeded` locally; assert subscription row state.
   - `two-factor.spec.ts` — enable 2FA → log out → log in → challenge step appears → correct TOTP succeeds.
4. **D3 — CI security job strictness.** Audit `.github/workflows/ci.yml` `security` job: confirm `npm audit --audit-level=moderate` and `composer audit --format=json` (or equivalent) cause job failure, not just warning.
5. **O4 — `.env.example` completeness.** Write a small test or shell script that diffs `grep "env(" config/ -r` vs. `.env.example`; flag any missing keys.

### Phase 3 — Sole-op polish (1–2 weeks, lower urgency)

6. **I2 — `docker-compose.dev.yml`.** MySQL 8, Redis 7, Mailpit. Add `composer dev` alternative command in the README that uses the compose stack.
7. **T4 — SEO invariant auto-discovery.** Extract a `PublicRouteRegistry` class (or `RouteServiceProvider::publicRoutes()`) enumerating routes with a known attribute, then:
   - `SeoController::buildSitemapUrls()` iterates it.
   - The three SEO test files iterate it via `dataset()`.
   - Result: adding a new public route is a single-file change.
8. **I5 — Cache Playwright install in CI.** Use `actions/cache` on `~/.cache/ms-playwright`. ~10 minutes of work; one of those "do it next time you touch CI" items.
9. **N1 — Split `web.php` into domain files.** `routes/marketing.php` (public/SEO), `routes/app.php` (authenticated), `routes/dev.php` (health, sitemap). 81 routes in one file is enough friction that splitting starts paying back.
10. **Doc1 — Consolidate `docs/`.** Write `docs/README.md` as a single entry point listing the purpose of each doc. Then merge overlapping pairs:
    - `AI_DEVELOPMENT_SAFEGUARDS.md` + `PROACTIVE_SAFEGUARDS_SUMMARY.md` + `IMPLEMENTATION_GUARDRAILS.md` + `PLANNING_CHECKLIST.md` → one `docs/AI_WORKFLOW.md`.
    - `architecture-review-sole-operator.md` + `SYSTEM_DESIGN_SOLO_OPERATOR.md` + `architecture-removal-plan.md` → one rolling `docs/ARCHITECTURE.md` + one `docs/adr/` entry per historical decision.
    - `TEST_PHASE1_COMPLETION.md` is a milestone artifact; move to `docs/archive/` or delete.

### Phase 4 — Continuous (address when touching)

11. **C4** — `AdminBillingStatsService` split, when next touching admin billing metrics.
12. **C5** — Marketing page refactor, only if you find yourself reusing the guides across multiple forks. If you delete them per-fork (per Doc2), this debt evaporates.
13. **C6** — Keep moving business logic from controllers into services as you touch each controller.
14. **A4** — Watch Cashier v17 release notes; property-access changes in `Subscription` could break.
15. **Doc4** — Write an ADR for each *new* non-obvious decision. Don't backfill old ones unless the decision comes up again.
16. **T5/Doc3** — Annotate the 1 contract test (`FeatureFlagContractTest`) with a PHPDoc block pointing to the ADR that justifies the invariant.

## Non-debt (worth restating since context has changed)

Several things that could look debt-adjacent in the current state are deliberate and correct:

- **Lifecycle simplification is net-positive.** Removing scoring, UTM, GA4 forwarding, and multi-stage email campaigns reduced real complexity. The `AuditService::log()` → `PersistAuditLog` job pipeline is the right level of abstraction for a sole-op running <1000-user SaaS products.
- **`laravel/horizon` and `laravel/socialite` in `suggest`.** Correct — opt-in per fork.
- **`phpstan-baseline.neon` exists.** Having a baseline is fine; treating it as permanent is the debt. Shrink it, don't try to eliminate it.
- **Contract tests directory contains exactly 1 file (`FeatureFlagContractTest`).** Fine. Not every codebase needs heavy contract-test surface.
- **Single-tenant architecture.** Explicitly correct per `CLAUDE.md`; don't change.
- **No SSR.** Documented choice (per `.claude/rules/seo.md`); SEO shell covers crawlers.

## Prioritized cut-list (top 10)

| Priority | Item | Phase |
|----------|------|-------|
| 36 | Doc2 — `docs/FORKING.md` + `scripts/new-saas.sh` | 1 |
| 28 | T2 — PHPStan baseline reduction via `laravel-ide-helper` | 2 |
| 28 | I1 — Synthetic production monitor + `MONITORING.md` | 2 |
| 25 | D3 — CI security job fails on moderate+ | 0 |
| 25 | I3 — `docs/OPS.md` with required branch-protection checks | 0 |
| 25 | A4 — Cashier changelog watch (zero work, high-risk touchpoint) | continuous |
| 20 | O4 — `.env.example` completeness test | 2 |
| 20 | I2 — `docker-compose.dev.yml` | 3 |
| 20 | I4 — VPS multi-product runbook | 3 |
| 18 | T4 — SEO invariant auto-discovery | 3 |

## Recurring hygiene (add to quarterly calendar)

1. `composer outdated` + `npm outdated`; bump non-major where CI stays green.
2. Re-read `phpstan-baseline.neon`; delete entries that no longer match (dead code removed).
3. Count AGENT_REVIEW / IMPLEMENTATION_REPORT / PRE_FLIGHT / VERIFY_DONE files in repo root — if over 20, purge. (Currently 3.)
4. `php artisan test --coverage` on billing/webhook paths — if under 90% for those, add tests before next feature work.
5. Dependabot PRs — review and merge.
6. Re-read `.claude/rules/*.md` and `docs/ARCHITECTURE.md`; prune anything now stale.
