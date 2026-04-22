# Architecture Review — Sole-Operator SaaS Base

**Date:** 2026-04-22
**Scope:** `laravel-react-starter` evaluated as the base for multiple SaaS projects shipped and maintained by one person.
**Primary concerns:** Maintenance burden & complexity. Time-to-ship on new ideas.
**Stage:** One project in flight on the starter.

---

## TL;DR

This starter is genuinely impressive engineering. It is also **oversized for a sole-operator SaaS base**. The problem isn't any individual piece — it's that you have to *understand, test, and keep alive* ~30 distinct subsystems before you've written product code. Every one of them is a debt source on the day a customer reports a bug in the one you haven't touched in six months.

The right move for you is not to rewrite it. It's to **aggressively turn things off by default**, cut two or three subsystems entirely, and separate "starter concerns" from "first-project concerns." Concrete recommendations below, grouped as **Keep**, **Defer** (code stays, flag stays off), and **Cut/Rethink** (remove or consciously replace).

### The raw shape you're carrying

| Surface | Count | Comment |
|---|---|---|
| PHP app LOC | ~20,270 | Pre-product |
| TS/TSX LOC | ~66,920 | Pre-product |
| Test files | 230 | ~$X/month of your time just to keep green |
| Artisan commands | 17 | 8 are lifecycle email senders |
| Services | 20 | Scoring, lifecycle, analytics, audit, cohorts… |
| Admin controllers | 26 | An admin panel is a mini-product |
| Middleware | 12 | |
| Config files | 29 | |
| Migrations | 45 | |
| Feature flags | 12 | Each one is a branch in test state-space |
| Radix primitives imported | 23 | Dormant cost, fine |

Every number above is a **liability per year per month of your time**, not an asset. The question is whether the feature it enables is worth more than the maintenance cost for a solo dev without PMF yet.

---

## The honest diagnosis

Three failure modes I'd flag before anything else:

**1. "Mini-product syndrome."** The admin panel (26 controllers, 22 React page dirs, its own routes file, cache invalidation enum, 5-min TTL stats) is a product on its own. So is the marketing surface (Blog, Guides, Compare, Changelog, Roadmap, SEO shell, IndexNow, SSR build, JSON-LD validity tests). So is the lifecycle/scoring stack (EngagementScoring, LeadScoring, CustomerHealth, Cohort, NPS, UserStageHistory, AnalyticsGateway, 8 lifecycle email commands). Each of those would be a reasonable sprint goal for a three-person team. As a sole operator you have three of them *already*, unused, waiting to break.

**2. "The quality apparatus costs more than the code."** PHPStan + Pint + Pest + Vitest + Playwright + **Infection (mutation testing)** + Husky + lint-staged + CI gates + ADRs + path-scoped rules + contract tests + test quality rules. This is the toolchain of a 20-person engineering org. It's not wrong, but for a sole op it is **another product you're maintaining** on top of your SaaS. Specifically Infection and Scribe have ongoing cost disproportionate to solo-op benefit.

**3. "Feature flag tax on every change."** The testing rules doc flags this directly: "Routes conditionally registered at boot time cannot be tested for both enabled/disabled states in the same test suite." Every flag is now a branch in your thinking and your test matrix. You have 12. For a sole op, git branches and `.env` are often cleaner than runtime flags — flags shine when you want to toggle behavior *per customer* or *per deploy slice*, neither of which you need yet.

---

## Keep (these earn their spot for a sole-op SaaS)

- **Billing (`BillingService` + Redis locks + Cashier)** — money first. The lock + eager-load pattern is production-grade and would take you weeks to rewrite. The `billing.md` gotchas doc is gold.
- **Auth + 2FA + email verification + session tracking** — baseline credibility. `laragear/two-factor` is the right call.
- **Sentry + `/health` endpoint + Horizon (optional)** — you cannot operate without visibility. Keep Sentry on day 1.
- **Feature flags *as a concept*** — just use fewer of them and default more OFF (see below).
- **The accessibility and billing path-scoped rules** — these represent real knowledge, keep them.
- **Pest + Vitest + Playwright smoke tests** — core test stack is right-sized.
- **PHPStan + Pint** — cheap, high value.
- **Ziggy + Inertia + Breeze + Radix UI kit** — the frontend foundation is solid and fast to build on.
- **Single-tenant-by-default stance** — explicit and documented is correct. Just be *aware* that most SaaS eventually wants teams/orgs, and retrofitting tenancy is painful (see Cross-cutting).

---

## Defer (keep the code, flag OFF, come back when you have signal)

These are legitimately useful *eventually*, but on a pre-PMF solo project they buy you nothing and cost you attention every time you pull main.

| Flag / subsystem | Why defer |
|---|---|
| `webhooks.enabled` | No one integrates with a pre-PMF product. Ship the MVP, add when the first customer asks. |
| `notifications.enabled` | In-app notifications only matter once users return. Email is enough until then. |
| `api_tokens.enabled` | Public API is a product surface. Deliberate decision, not default. |
| `api_docs.enabled` (Scribe) | No API consumers yet → no docs needed. Also avoids Scribe dev-dep upkeep. |
| `indexnow.enabled` + sitemap pinging | Requires a product people search for. Month 6+, not day 1. |
| `onboarding.enabled` | Fine as default-on, but the wizard itself usually needs rewriting per product — don't over-invest in the shared version. |
| Lifecycle email commands beyond `send-welcome` | Dunning, re-engagement, win-back, trial-nudges, trial-ending — these all require **real user data** to be worth sending. Stub them, wire them, leave them off. |
| Admin pages beyond `Users`, `FailedJobs`, `Billing`, `Health`, `AuditLogs`, `FeatureFlags` | The other 20 admin pages (NpsResponses, Cache, EmailSendLogs, Sessions, SocialAuth, IndexNow, Roadmap, ContactSubmissions, Feedback, ProductAnalytics, Schedule, …) are 90% nice-to-have. Hide them behind the admin flag dependency check and don't touch them. |

**Concrete action:** in `config/features.php`, flip every flag *except* `email_verification`, `user_settings`, `social_auth`, and `admin` (minimal admin) to `false` by default. Currently `social_auth`, `email_verification`, `api_tokens`, `user_settings`, `onboarding` all default-on — too much.

---

## Cut or rethink (complexity tax > sole-op benefit)

**1. The lifecycle/scoring stack.** `EngagementScoringService`, `LeadScoringService`, `CustomerHealthService`, `CohortService`, `ProductAnalyticsService`, `UserStageHistory`, `AnalyticsGateway`, `AnalyticsEvent` enum, `DispatchAnalyticsEvent` job, `CaptureUtmParameters` middleware, `analytics-thresholds.php`. This is a **marketing-analytics platform grafted onto a Laravel app**. For a sole op pre-scale, a single `users.last_seen_at` column and one Mixpanel/PostHog snippet does 95% of this at 1% of the maintenance cost.
   - **Recommendation:** Extract to a separate package or delete entirely from the starter. Add back per-project when you have ≥50 paying customers to segment.

**2. Public marketing surface in the starter.** `BlogController`, `GuidesController`, `CompareController`, `ChangelogController`, `RoadmapController`, `LegalController`, `FeaturesController`, plus the SEO shell, sitemap builder, SSR build, IndexNow, JSON-LD validity tests, and three dedicated SEO test files.
   - This is a **marketing site**. Marketing sites want to be editable without deploying, live on a CDN, and look different per product. Putting them in the app means every product built on this starter ships the same `/blog` route, same `/compare` page, same SSR config.
   - **Recommendation:** Move this stack to a *separate* starter (or a Nextra/Astro/Fumadocs marketing repo). The app should handle auth/app/billing/admin. Landing page + pricing can stay in the app as lightweight pages; everything else is a separate surface.

**3. SSR + SEO shell hybrid.** You have SSR wired (`vite build --ssr` + `bootstrap/ssr/ssr.mjs`) *and* a Blade "SEO shell" that hides H1s and breadcrumbs in a `hidden` div as a crawl fallback. That's **two code paths** doing the same job.
   - **Recommendation:** Pick one. For a sole op, I'd pick the Blade shell (no Node process to run in prod), disable SSR entirely, and delete the SSR build step. Trade: you lose fast FCP for authed pages, which doesn't matter for a crawler and doesn't matter behind auth.

**4. Infection (mutation testing) + Scribe + parts of the admin audit tooling.** These are team-scale investments. Scribe doc-gen is useful when you have API consumers; Infection is useful when you have teammates who need a "killed mutants" gate. Both cost you time on every CI run and every dep bump.
   - **Recommendation:** Remove Infection from `require-dev` and CI. Keep Scribe only if you flip `api_tokens` on.

**5. Horizon as a suggested dep.** For a single VPS sole-op deploy, `supervisord` running `php artisan queue:work` with Sentry for errors is ~10 lines of config and zero ongoing UI to maintain.
   - **Recommendation:** Document "you don't need Horizon unless you're running >1 worker + want the dashboard." Ship without it in the starter.

**6. `AdminImpersonationController` + `SessionDataMigrationService`.** Both useful in theory; both are scalpels with self-inflicted-wound potential (impersonation leaking into analytics, session migration during deploys). For sole op, you can SSH to tinker faster than you can safely operate impersonation.
   - **Recommendation:** Cut impersonation from the default starter; keep in a branch for the one project that actually needs it.

---

## Cross-cutting concerns

**Tenancy is a one-way door.** CLAUDE.md pins "Do not add account/org/workspace scoping unless explicitly requested." That's the right default for *simplicity*, but ~70% of B2B SaaS ideas end up needing teams. Retrofitting `tenant_id` across 16 models and 45 migrations is a multi-week job you'll do mid-crisis.
   - **Recommendation:** When starting a new project on this base, make a conscious day-1 call: "is this definitely single-user forever, or could it grow teams?" If *maybe*, add a `team_id` nullable FK to `users` and scope queries through a `CurrentTeam` helper from the start. It costs days one; it saves weeks year one.

**Redis is a single point of failure.** Billing locks, admin cache, queue, Horizon all lean on it. Fine operationally — just make sure your VPS failover story includes Redis, and that `/health` surfaces Redis connectivity (it should; check `HealthCheckService`).

**Feature flag vs branch trade-off.** Solo-op workflow that's usually better than runtime flags: a short-lived git branch per optional subsystem, merged into a project-specific base when you want that subsystem in *that* project. You lose "flip off in prod," you gain "this flag doesn't exist in the codebase for projects that don't need it."
   - **Compromise:** Keep flags for subsystems you might want to toggle *in production without deploy* (e.g., `billing.coming_soon`). Drop flags for subsystems you'd never hot-toggle (e.g., `admin`, `api_docs`, `indexnow` — those are "did we install this" flags, better as `composer require`).

**Test-suite ownership.** 230 tests is a lot but not alarming. The risk is that they ossify the starter. When you're editing a `User` model for project-specific reasons, a test in the starter failing on something you no longer care about is a tax. Keep the `tests/Contracts/` set genuinely immutable (the docs already warn not to modify without approval — good), but everything else should be fair game to delete per-project.

**The "11 feature flags with dependency graph" docs.** You have `FeatureFlagDependencyTest.php` and a dedicated `FEATURE_FLAGS.md`. That's overhead that signals the flag count is too high. Fewer flags → no dependency graph → no dependency test needed.

---

## Suggested "new-project day-1" configuration

When you spin up the *next* SaaS on this base, start here:

```env
# On:
FEATURE_BILLING=true            # if monetizing
FEATURE_EMAIL_VERIFICATION=true
FEATURE_USER_SETTINGS=true
FEATURE_ADMIN=true              # minimal admin only
FEATURE_TWO_FACTOR=true

# Off:
FEATURE_SOCIAL_AUTH=false       # add on specific demand
FEATURE_API_TOKENS=false
FEATURE_NOTIFICATIONS=false
FEATURE_ONBOARDING=false        # write per-product instead
FEATURE_WEBHOOKS=false
FEATURE_API_DOCS=false
FEATURE_INDEXNOW=false
```

Then in the first week of the new project:

1. **Delete** `app/Services/{EngagementScoring,LeadScoring,CustomerHealth,Cohort,ProductAnalytics,Session DataMigration}*.php` if you haven't globally purged them from the starter yet.
2. **Delete** the 8 non-welcome lifecycle email commands (`SendDunning`, `SendReEngagement`, `SendTrialNudges`, `SendTrialEnding`, `SendWinBack`, `SendOnboardingReminders`), and their tests.
3. **Delete** `BlogController`, `GuidesController`, `CompareController`, `ChangelogController`, `RoadmapController`, the SEO shell, IndexNow, and related tests *if* this project isn't a content-marketed product. Most SaaS apps aren't on day 1.
4. **Decide** tenancy (single-user forever vs. team-capable) before any business logic lands.
5. **Delete** `AdminImpersonationController` unless you know you'll need it.

---

## Net summary

The starter is ~3x bigger than it should be *for a sole operator*. The good news: it's also ~3x more credible-looking than a typical solo dev's base, and the parts you want (billing, auth, admin, infra) are production-grade. The operational move is to **shrink the starter** (or make aggressive per-project pruning a first-week ritual) rather than rewrite it. You're not fighting a bad architecture — you're fighting a *team-shaped* architecture on a solo team.

If you do only two things, do these:
1. **Extract or delete the marketing/SEO/content surface** from the app. It belongs in a separate repo.
2. **Delete the lifecycle/scoring stack** from the starter. Add it back, minimally, in the first project that reaches real user volume.

Those two cuts remove thousands of lines, dozens of tests, a whole admin section, and a meaningful chunk of what will otherwise break on you six months from now when you're trying to ship a new feature.
