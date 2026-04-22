# Tech Debt Audit — Laravel React Starter

**Audit date:** 2026-04-22
**Audited for:** Sole-operator use as a base template for multiple SaaS products
**Optimization lenses:** (a) Maintainability over 6–12 months, (b) Pre-launch hardening for paid customers, (c) Speed to first ship when forking the template

## TL;DR

The codebase is well-structured and more mature than most starters — feature flags, contract tests, PCOV-backed CI, Redis-locked billing, a 500-line PHPStan baseline, 230 Pest tests, 401 Vitest tests. The debt that actually matters for a sole operator is clustered in three places:

1. **Operational hygiene** — 18 one-off audit/review JSON files are committed to git; ~240 AI-generated report files sit in the repo root (gitignored but local clutter); 83 PHPStan errors are baselined rather than fixed.
2. **Missing sole-op scaffolding** — there is no forking playbook, no `scripts/new-saas.sh`, no `docker-compose.dev.yml`, no synthetic production monitor beyond `/up`. These are the single biggest accelerators for the "stand up SaaS #2, #3, #4 from this base" use case.
3. **Revenue-path test coverage is thin** — E2E only covers auth smoke. Billing, webhooks, and 2FA (the three areas that silently cost money when they break) have no end-to-end protection.

Everything else — oversize services, 571-line controllers, 1,000+ line marketing pages, doc sprawl — is real debt but it compounds slowly and is safe to address lazily when you next touch the code.

## Scoring methodology

Each item is scored per the standard framework:

- **Impact (1–5):** how much this slows you down or compounds over time
- **Risk (1–5):** what happens to revenue, security, uptime, or data if you don't fix it
- **Effort (1–5):** 1 ≈ minutes, 2 ≈ hours, 3 ≈ one day, 4 ≈ several days, 5 ≈ a week+

**Priority = (Impact + Risk) × (6 − Effort).** Higher is more urgent.

## Prioritized debt inventory

### Code debt

| # | Item | Location | Impact | Risk | Effort | Priority |
|---|------|----------|--------|------|--------|----------|
| C1 | `BillingService` spans 16 public methods across resolution, mutations, coupons, previews, portal — one class doing too much | `app/Services/BillingService.php` (486 lines) | 3 | 3 | 4 | 12 |
| C2 | `SubscriptionController` is a 571-line controller with 11 actions; Laravel convention is single-action controllers when this thick | `app/Http/Controllers/Billing/SubscriptionController.php` | 2 | 2 | 3 | 12 |
| C3 | `FeatureFlagService` mixes evaluation, admin override management, and experiment variant assignment — three distinct concerns | `app/Services/FeatureFlagService.php` (496 lines) | 2 | 2 | 4 | 8 |
| C4 | `AdminBillingStatsService` (513 lines) mixes aggregate metrics, cohort retention, and pagination — reasonable to split into Reader + Paginator | `app/Services/AdminBillingStatsService.php` | 2 | 2 | 3 | 12 |
| C5 | 8 React page files over 800 lines (BuildVsBuyGuide 1,134; SaasStarterKitComparison 1,104; NextjsSaas 995; TenancyArchitectureGuide 961; Pricing 940; LaravelSaasGuide 886; Welcome 877; StripeBillingGuide 827). Hurts Vite HMR and IDE responsiveness | `resources/js/Pages/**` | 2 | 1 | 3 | 9 |
| C6 | Controllers-to-services ratio 74:20 — business logic still lives in several controllers despite `BillingService` pattern existing as precedent | `app/Http/Controllers/**` | 2 | 2 | 4 | 8 |

### Test debt

| # | Item | Location | Impact | Risk | Effort | Priority |
|---|------|----------|--------|------|--------|----------|
| T1 | E2E coverage is auth-only. Billing checkout, cancel, resume, swap, and webhook receipt have no end-to-end test | `tests/e2e/` | 4 | 4 | 4 | 16 |
| T2 | 83 errors suppressed in `phpstan-baseline.neon` (499 lines). Dominant patterns: Cashier model dynamic properties, Carbon-vs-string in lifecycle commands, unnecessary nullsafe, unresolvable Collection::map callbacks | `phpstan-baseline.neon` | 4 | 3 | 2 | 28 |
| T3 | Lifecycle commands carry real type bugs (String→Carbon in `CheckExpiredTrials`, `SendTrialNudges`). These run trials/dunning — bugs here lose revenue silently | `app/Console/Commands/*.php` (multiple) | 2 | 3 | 2 | 20 |
| T4 | SEO invariants rely on three separate tests + `SeoController::buildSitemap()` being updated when any public route is added. Easy to forget, regressions are externally visible | `tests/Feature/Seo/*.php`, `SeoController` | 3 | 3 | 3 | 18 |
| T5 | Contract tests in `tests/Contracts/` are marked "do not modify without approval" — fine as a guardrail, but the invariants they protect aren't indexed anywhere. Risk: you break one and accept it as "obviously wrong test" without understanding what it encoded | `tests/Contracts/` + `CLAUDE.md` | 2 | 3 | 2 | 20 |
| T6 | Mutation testing (`infection`) is referenced in `CLAUDE.md` but isn't in `composer.json`. Either remove the reference or add and wire it | `composer.json`, `CLAUDE.md` | 1 | 1 | 2 | 8 |

### Dependency debt

| # | Item | Location | Impact | Risk | Effort | Priority |
|---|------|----------|--------|------|--------|----------|
| D1 | `phpunit/phpunit` ^12.0 is in `require-dev` alongside Pest 4. Pest wraps PHPUnit but the explicit PHPUnit dep is a redundancy unless directly invoked | `composer.json` | 1 | 1 | 1 | 10 |
| D2 | `axios` ^1.11 in `devDependencies`. Inertia handles XHR — confirm it's actually used (search imports) and either move to `dependencies` or remove | `package.json` | 1 | 2 | 1 | 15 |
| D3 | No `composer audit` / `npm audit` fail-the-build policy visible — the CI job "security" runs but verify it's non-blocking only on info-level findings, not moderate/high | `.github/workflows/ci.yml` (job: `security`) | 2 | 3 | 1 | 25 |

### Documentation debt

| # | Item | Location | Impact | Risk | Effort | Priority |
|---|------|----------|--------|------|--------|----------|
| Doc1 | Overlapping guidance across `docs/AI_DEVELOPMENT_SAFEGUARDS.md`, `docs/IMPLEMENTATION_GUARDRAILS.md`, `docs/PLANNING_CHECKLIST.md`, `docs/AI_PROMPT_TEMPLATES.md`, `docs/PROACTIVE_SAFEGUARDS_SUMMARY.md`. Six files, lots of duplication | `docs/` | 2 | 1 | 2 | 12 |
| Doc2 | **No forking playbook.** Zero instruction on how to clone this as SaaS #2 — what to rename, which pricing tiers to zero out, which feature flags to flip, how to reset the AI-generated report clutter, where product branding lives | (missing) | **5** | **4** | **2** | **36** |
| Doc3 | Contract tests protect invariants that aren't documented — see T5. Each contract test should link to a one-paragraph ADR explaining "what breaks in production if this test changes" | `tests/Contracts/` + `docs/adr/` | 2 | 3 | 2 | 20 |
| Doc4 | ADR count in `docs/adr/` is 4 — plenty of decisions in this repo (feature flag registry, Redis locks on billing, boot-time route registration limitation, two-phase migration policy) haven't been captured as ADRs | `docs/adr/` | 2 | 2 | 3 | 12 |

### Infrastructure debt

| # | Item | Location | Impact | Risk | Effort | Priority |
|---|------|----------|--------|------|--------|----------|
| I1 | **No synthetic production monitor.** `/up` and `/health` exist but nothing is calling them. A paid SaaS going silently down is a category-defining sole-op failure mode | (missing) | 3 | 4 | 2 | 28 |
| I2 | **No `docker-compose.dev.yml`.** Every fresh clone requires provisioning MySQL, Redis, Mailpit locally. `CLAUDE.md` says "no Docker" but that's about production VPS deploy, not onboarding a new fork. A dev-only compose file cuts fork time from half a day to minutes | (missing) | 3 | 2 | 2 | 20 |
| I3 | No branch protection / required-checks configuration is visible in the repo (expected to be configured at GitHub level, but `deploy/` or a README ops section should name what must be green before merge) | (missing) | 2 | 3 | 1 | 25 |
| I4 | `deploy/` contains nginx + supervisor configs but no `ALLOWED_HOSTS`-style VPS firewall doc. For a sole-op running multiple SaaS on separate VPSes, a shared runbook would prevent drift | `deploy/`, `scripts/vps-setup.sh` | 2 | 3 | 2 | 20 |
| I5 | CI E2E job downloads Playwright Chromium on every run (no caching on the browser install step). Adds ~60s per CI run | `.github/workflows/ci.yml` (line ~359) | 1 | 1 | 1 | 10 |

### Architecture debt

| # | Item | Location | Impact | Risk | Effort | Priority |
|---|------|----------|--------|------|--------|----------|
| A1 | `web.php` has 55 routes in one file — approaching the pain threshold where splitting into `routes/marketing.php`, `routes/lifecycle.php`, `routes/public.php` starts paying back | `routes/web.php` | 2 | 1 | 2 | 12 |
| A2 | Boot-time feature-flag route registration creates a tested-in-one-mode-only limitation (documented in `.claude/rules/testing.md`). The `BillingFeatureFlagTest.php` exists only to mark itself skipped. Consider converting to conditional route groups at request time so both enabled/disabled paths are testable | `bootstrap/app.php`, `routes/web.php`, `tests/Feature/Billing/BillingFeatureFlagTest.php` | 3 | 2 | 4 | 10 |
| A3 | 130 React page files — when you fork for SaaS #2, the marketing guides (`BuildVsBuyGuide`, `LaravelSaasGuide`, `NextjsSaas`, `SaasStarterKitComparison`, etc.) are dead weight. No `marketing:clean` command or "unused pages" report | `resources/js/Pages/` | 3 | 1 | 2 | 16 |
| A4 | `Subscription` model extends `CashierSubscription` (19 lines) — tight coupling to Cashier internals. Monitor Cashier 17.x changelog for property access changes | `app/Models/Subscription.php` | 2 | 3 | 1 | 25 |

### Operational / repo hygiene

| # | Item | Location | Impact | Risk | Effort | Priority |
|---|------|----------|--------|------|--------|----------|
| O1 | **18 audit/review files are tracked in git.** `BILLING_REVIEWED_*.md` (1), `audit-*.json` (17). These were committed before the `.gitignore` patterns covering AGENT_REVIEW_/IMPLEMENTATION_REPORT_/PRE_FLIGHT_REPORT_/VERIFY_DONE_REPORT_/ADMIN_AUDIT_/GROWTH_AUDIT_/SEO_AUDIT_ patterns were added | repo root | 2 | 2 | 1 | 20 |
| O2 | ~240 AI-generated report files live in the repo root (gitignored, so they won't recommit, but they pollute tab-complete, IDE file pickers, `ls`, and any tar/zip of the working tree). No cleanup command | repo root | 2 | 1 | 1 | 15 |
| O3 | `.gitignore` covers most generated-report prefixes but misses `audit-*.json` and `BILLING_REVIEWED_*.md`. Add patterns | `.gitignore` (lines 69-110) | 1 | 1 | 1 | 10 |
| O4 | No `.env.example` audit verifying every `env()` reference in `config/*` has a corresponding documented default | `.env.example`, `config/**` | 2 | 3 | 2 | 20 |
| O5 | `v-prompts/`, `content/`, `template.json`, `boost.json` directories/files at root with no README explaining purpose. If any is dead, delete; if alive, document in the root README | repo root | 1 | 1 | 1 | 10 |

### Security / correctness quick-scan findings

- **Positive:** No `dd(`, `dump(`, `var_dump(` in `app/`. `console.log` only in `ErrorBoundary`, `CookieConsent`, marketing `Compare` pages — all defensible. No hardcoded secrets. `APP_DEBUG` defaults to false. All 307 `env()` calls in `config/` are correct usage.
- **No action items** in this category beyond what's covered elsewhere (D3 audit policy, I1 synthetic monitor, T3 lifecycle Carbon bugs).

## Phased remediation plan

This is structured so every phase can be interleaved with feature work — no "stop shipping to pay down debt" weeks.

### Phase 0 — Under 30 minutes (do this first)

These are free wins with trivial effort.

1. **O1 + O3 — Delete tracked audit files, extend `.gitignore`.**
   ```bash
   git rm BILLING_REVIEWED_*.md audit-*.json
   # Append to .gitignore:
   echo -e "\n# Orphaned AI audit outputs\naudit-*.json\nBILLING_REVIEWED_*.md" >> .gitignore
   git add .gitignore && git commit -m "chore: clean up orphaned audit files from repo root"
   ```
2. **O2 — Local cleanup of ~240 gitignored report files.**
   ```bash
   rm AGENT_REVIEW_*.md IMPLEMENTATION_REPORT_*.md PRE_FLIGHT_REPORT_*.md VERIFY_DONE_REPORT_*.md ADMIN_AUDIT_REPORT_*.json GROWTH_AUDIT_*.json SEO_AUDIT_*.json audit-*.json
   ```
3. **D1 — Drop `phpunit/phpunit` from `require-dev` if no direct imports:**
   ```bash
   grep -rE "use PHPUnit\\\\" tests/  # confirm no direct uses
   composer remove --dev phpunit/phpunit
   composer require --dev pestphp/pest-plugin-type-coverage  # optional: replaces value PHPUnit 12 added
   ```
4. **D2 — Check axios usage:**
   ```bash
   grep -rE "from ['\"]axios['\"]|require\(['\"]axios['\"]" resources/js/
   # If zero results: npm uninstall axios
   # If found in dependencies: move to package.json "dependencies"
   ```

### Phase 1 — Ship-speed foundations (1–2 days, highest priority items)

Target the two items that most directly accelerate "spin up SaaS #2."

5. **Doc2 — Write the forking playbook (priority 36).** Create `docs/FORKING.md` and `scripts/new-saas.sh`. The playbook should cover:
   - Branding swap-out checklist (`APP_NAME`, logo paths, OG image, `config/app.php` defaults, SEO title templates, Organization JSON-LD name)
   - Feature-flag starting point recommendation per SaaS archetype (B2C vs B2B vs internal)
   - Pricing tier reset (`config/cashier.php` or Stripe dashboard pointers)
   - Which marketing pages to delete (Build vs Buy guide, Laravel SaaS guide, etc., unless comparison content is reusable)
   - Contract test re-review prompt (see T5/Doc3)
   - First-deploy checklist cross-linking `scripts/vps-setup.sh`

   The `scripts/new-saas.sh` should take a target directory + brand name and do the mechanical rewrites (find/replace of `laravel-react-starter`, `APP_NAME`, email addresses) plus delete the ~240 stale report files.

6. **I2 — Write `docker-compose.dev.yml` for local development only.** MySQL 8, Redis, Mailpit. Document in README as "optional — run `docker compose up` instead of Herd/native if you prefer isolation per fork." This doesn't contradict the "no Docker in production" stance.

7. **T3 — Fix lifecycle command type bugs.** Concrete fix: in `User` model `casts()` method, add `'trial_ends_at' => 'datetime'`, `'trial_starts_at' => 'datetime'`. This removes 2 of the 83 PHPStan baseline entries AND closes a real bug class where trial expiry comparison against strings silently fails.

### Phase 2 — Pre-launch hardening (1–2 weeks)

Blocks for shipping a paid product to real customers.

8. **T2 — Reduce PHPStan baseline by ~70%.** Install `barryvdh/laravel-ide-helper` in `require-dev`, run `php artisan ide-helper:models -W`, run `php artisan ide-helper:generate`. Then for each remaining baseline entry, decide: fix properly, convert to `@phpstan-ignore-next-line` inline with a comment explaining why, or accept as architectural (rare — most entries here are missing PHPDoc or a missing cast).

   Target: baseline drops from 499 lines to under 150. Remaining entries get comments linking to a GitHub issue or "won't fix, tracked by ADR-X".

9. **I1 — Wire synthetic monitoring.** Add Uptime Kuma or Better Stack check against `/health` with token auth (health.php already supports this). Document in `deploy/MONITORING.md`. This is the single most important production-readiness gap.

10. **T1 — Playwright E2E for revenue-critical flows.** At minimum:
    - Checkout happy path (free → pro via Stripe test card `4242...`)
    - Subscription cancel (retain access until period end)
    - Webhook receipt (`invoice.payment_succeeded` → subscription `active`)
    - 2FA enrollment + challenge

    Run against Stripe's webhook CLI in CI. Sign each test with the test secret from `config/webhooks.php`.

11. **T4 — SEO invariant auto-discovery.** Replace the three parallel test files' hardcoded route lists with a single enumeration derived from `RouteServiceProvider::$publicRoutes` (add this property). Auto-include in `buildSitemap()` too. Result: adding a public route is one-file-change instead of four.

12. **D3 — CI `security` job must fail the build on moderate+.** Confirm the existing `.github/workflows/ci.yml` job runs with `--audit-level=moderate` (npm) and `composer audit --abandoned=fail` equivalents.

13. **I3 — Document required branch-protection checks in `docs/OPS.md`.** Name which CI jobs must be green before merge (php-tests, js-tests, build, code-quality, e2e-tests). This is a GitHub-side config but the source of truth belongs in the repo.

### Phase 3 — Done alongside feature work (continuous, no dedicated time block)

14. **C1 — Split `BillingService`** the next time you touch billing. Recommended split: `SubscriptionMutator` (cancel/resume/swap/updateQuantity), `TierResolver` (resolveUserTier/resolveTierFromPrice/isUpgrade), `CouponValidator`, `BillingPortalService`. Keep `BillingService` as a facade for backward compatibility in one release, then deprecate.

15. **C2 — Split `SubscriptionController`** into single-action controllers (`CheckoutController`, `SubscribeController`, `CancelController`, etc.) when you next modify a billing route. Follow Laravel convention, reduces merge conflict surface, and makes per-action authorization/middleware explicit.

16. **C4 — `AdminBillingStatsService` → `AdminBillingStatsReader` + `AdminBillingSubscriptionPaginator`.** Minor split but clarifies the two use cases (dashboard metrics vs. paginated list view) that currently share a class.

17. **C3 — Extract `FeatureFlagAdminService`** from `FeatureFlagService` (move `setGlobalOverride`, `removeGlobalOverride`, `setUserOverride`, `removeUserOverride`, `removeAllUserOverrides`, `searchUsers`, `getAdminSummary`). Keeps runtime-hot `resolve()` path lean.

18. **Doc1 — Consolidate AI documentation** into one `docs/AI_WORKFLOW.md` the next time you update any AI doc. Source of truth: a single file with sections for Planning, Implementation, Testing, Debugging, Prompt Templates.

19. **Doc4 — Backfill ADRs** for each new decision — don't retroactively write 10 ADRs, but commit to the rule "if the decision has been debated once, write the ADR before shipping."

20. **Doc3 + T5 — Link each contract test to an ADR.** Not retroactively; when you next open a contract test file, add a PHPDoc block at the top citing the ADR that justifies the invariant. Over 3–6 months all contract tests get annotated.

21. **C5 — Marketing page refactor to MDX or section-components** only if you end up running more than one SaaS where these guides are reused. If you delete the guides in each fork (per Doc2), this debt evaporates.

22. **A1 — Domain-scoped route files** when `web.php` crosses ~75 routes. Not now; monitor.

23. **A2 — Conditional route groups at request-time** — real refactor, several days. Only pay this down when you hit a concrete bug caused by the boot-time limitation.

24. **I5 — Cache Playwright browser install in CI.** 10-minute fix, do it the next time you touch the CI file for any reason.

## What's NOT debt (worth stating explicitly)

Several things that could look like debt on a quick scan are actually correct choices for this project and a sole-operator context:

- **No Docker for production.** VPS-based deploys are intentional; reduces ops surface.
- **Single-tenant.** Don't add workspace/org scoping until a product requires it.
- **55 scheduled skipped tests.** 51 of those are conditional Playwright `test.skip(projectName !== 'chromium-desktop')` — legitimate multi-project gating, not test debt. The remaining 4 are feature-flag-gated in the test setup.
- **`laravel/socialite` and `laravel/horizon` in `suggest` (not required).** Correct — the starter is designed so you opt in per fork.
- **Pest 4 + Vitest + Playwright** — three frameworks, but each covers a distinct tier (unit/integration, component, E2E). Not redundancy.
- **11 feature flags.** Feels like a lot, but each gates a genuinely swappable subsystem.
- **Heavy `.claude/rules/*.md` coverage** (accessibility, billing, frontend, lifecycle, migrations, seo, testing, webhooks). Real value for AI-assisted work — this is the opposite of debt.

## Recurring hygiene (add to quarterly calendar)

Schedule a 2-hour debt sweep every quarter. Agenda:

1. Re-run `composer outdated` + `npm outdated`; bump non-major where CI stays green.
2. Re-read `phpstan-baseline.neon` and delete any entries that no longer match (dead code has been removed).
3. Count AGENT_REVIEW/IMPLEMENTATION_REPORT files in repo root — if over 20, purge.
4. Re-run `php artisan test --coverage` on billing/webhook paths. If under 90% for those, add tests before next feature work.
5. Dependabot PRs — review and merge.

## Summary of priorities (sorted)

| Priority | Item | Category | Phase |
|----------|------|----------|-------|
| 36 | Doc2 — Forking playbook + `scripts/new-saas.sh` | Docs | 1 |
| 28 | T2 — Reduce PHPStan baseline (install laravel-ide-helper) | Tests | 2 |
| 28 | I1 — Synthetic production monitor | Infra | 2 |
| 25 | D3 — CI security audit fails at moderate+ | Deps | 2 |
| 25 | I3 — Branch-protection required-checks doc | Infra | 2 |
| 25 | A4 — Cashier changelog watch | Arch | continuous |
| 20 | O1 — Delete tracked audit files | Hygiene | 0 |
| 20 | O4 — Verify `.env.example` completeness | Hygiene | 2 |
| 20 | T3 — Lifecycle Carbon/string casts | Tests | 1 |
| 20 | T5/Doc3 — Contract test ↔ ADR linking | Tests/Docs | 3 |
| 20 | I2 — `docker-compose.dev.yml` | Infra | 1 |
| 20 | I4 — VPS runbook | Infra | 2 |
| 18 | T4 — SEO invariant auto-discovery | Tests | 2 |
| 16 | T1 — Billing/webhook/2FA E2E coverage | Tests | 2 |
| 16 | A3 — Dead-marketing-page report for forks | Arch | continuous |
| 15 | O2 — Clear ~240 gitignored reports locally | Hygiene | 0 |
| 15 | D2 — Resolve axios status | Deps | 0 |
| 12 | C1 — Split `BillingService` | Code | 3 |
| 12 | C2 — Single-action controllers for billing | Code | 3 |
| 12 | C4 — Split `AdminBillingStatsService` | Code | 3 |
| 12 | Doc1 — Consolidate AI docs | Docs | 3 |
| 12 | Doc4 — ADR backfill | Docs | 3 |
| 12 | A1 — Domain-scoped route files | Arch | 3 |
| 10 | D1 — Drop phpunit/phpunit from require-dev | Deps | 0 |
| 10 | A2 — Request-time feature-flag route registration | Arch | 3 |
| 10 | I5 — Cache Playwright install in CI | Infra | 3 |
| 10 | O3 — Extend `.gitignore` | Hygiene | 0 |
| 10 | O5 — README for root directories | Hygiene | 0 |
| 9 | C5 — Marketing page refactor to MDX | Code | 3 |
| 8 | C3 — Extract `FeatureFlagAdminService` | Code | 3 |
| 8 | C6 — Controller-logic-to-service migration | Code | 3 |
| 8 | T6 — Remove or add `infection` | Tests | 3 |

**Top 5 to execute first (Phase 0 + start of Phase 1):**

1. Doc2 — Forking playbook (36)
2. T2 — PHPStan baseline (28)
3. I1 — Synthetic monitor (28)
4. O1 + O2 + O3 — Clean audit files and extend .gitignore (trivial, do together)
5. T3 — Lifecycle Carbon casts (20, also knocks out 4–6 PHPStan baseline entries)
