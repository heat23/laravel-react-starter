# System Design Review — Laravel React Starter for a Sole Operator

**Scope:** End-to-end architecture review of the starter template, optimized for a single person running SaaS products that each target up to ~1,000 users.
**Reviewer perspective:** What breaks at 2 a.m.? What costs money you don't need to spend? Where does the template already carry you vs. where will you trip?

---

## 1. Requirements You're Actually Optimizing For

| Dimension | Target |
|---|---|
| Users per product | 0–1,000 (indie / lifestyle) |
| Operator count | 1 (you) |
| Uptime goal | ~99.5% (≈3.6 hours/month downtime is acceptable) |
| Cost ceiling per product | $50–150/mo all-in |
| Time to ship a new feature | Hours, not days |
| Time to recover from a failure | Minutes, not hours |
| Cognitive load | Low — fits in one person's head |
| Revenue mechanics | Subscription billing, email lifecycle, minimal manual touch |

Implication: bias every design choice toward *managed services, narrow feature surface, and reversible decisions*.

---

## 2. What This Starter Gets Right for You

The template already makes most of the right calls for a sole operator. Worth naming explicitly before critiquing anything.

- **Single-tenant by default.** Tenancy is the #1 source of accidental complexity in SaaS. CLAUDE.md explicitly prohibits adding account/org scoping without request. Keep this. If you ever need teams, pick a moment and do it deliberately, not by accretion.
- **No Docker in prod.** VPS + supervisor + nginx is the right call at this scale. Kubernetes is a second full-time job.
- **Feature flags as first-class (`config/features.php`).** 11 subsystems gated by env var. You can ship with just email verification and user settings, and everything else stays dark. This is the single most important lever you have for keeping cognitive load down.
- **Opinionated AI-agent safeguards** (`docs/PLANNING_CHECKLIST.md`, `IMPLEMENTATION_GUARDRAILS.md`, `.claude/rules/*`, contract tests). As a solo operator, AI-assisted development *is* your team. Infrastructure that keeps an agent from breaking production pays for itself weekly.
- **Quality gates already wired.** Pest parallel + PHPStan + Pint + Vitest + Infection + pre-commit hooks + CI. You don't need to build a testing culture — it's installed.
- **Billing done correctly.** Redis-locked mutations, eager loading on Cashier operations, HMAC-verified webhooks, incomplete-payment reminders. This is the most common place solo SaaS loses money silently. Already handled.
- **Lifecycle email infrastructure.** Dunning, onboarding, trial nudges, win-back, re-engagement. `EmailSendLog` dedup. At your scale, these commands are revenue multipliers — each one you enable is worth more than almost any feature you'd write.
- **Admin panel as primary debugging UI.** When something's wrong with a user at 2 a.m., `/admin` is where you triage. Invest here first, not in fancier logs.

---

## 3. High-Level Architecture

```
                                ┌───────────────────┐
                                │      CloudFlare   │  ← TLS, caching, WAF, DDoS (add this day 1)
                                └─────────┬─────────┘
                                          │
                  ┌───────────────────────┴───────────────────────┐
                  │                    Nginx (VPS)                │
                  │  gzip, static cache, trusted-proxy headers    │
                  └───────────────────────┬───────────────────────┘
                                          │
    ┌────────────────┬────────────────────┼────────────────────┬────────────────┐
    │                │                    │                    │                │
    ▼                ▼                    ▼                    ▼                ▼
┌────────┐   ┌─────────────┐       ┌─────────────┐     ┌──────────────┐   ┌─────────────┐
│ PHP-FPM│   │  Inertia SSR│       │   Queue     │     │  Scheduler   │   │  /health    │
│ Laravel│   │  (Node)     │       │   Worker    │     │  (cron)      │   │ (token-auth)│
│        │   │  optional   │       │  (supervisor│     │  routes/     │   └─────────────┘
└───┬────┘   └──────┬──────┘       │  single q)  │     │  console.php │
    │               │              └──────┬──────┘     └──────┬───────┘
    └───────────────┴─────────────────────┼───────────────────┘
                                          │
              ┌──────────────┬────────────┼────────────┬──────────────┐
              ▼              ▼            ▼            ▼              ▼
         ┌────────┐    ┌─────────┐   ┌────────┐   ┌─────────┐   ┌───────────┐
         │MySQL 8 │    │  Redis  │   │ Stripe │   │Postmark │   │  Sentry   │
         │(managed│    │(cache + │   │(billing│   │  (tx    │   │ (errors)  │
         │ or co- │    │ locks + │   │+ webhk)│   │  email) │   └───────────┘
         │located)│    │ session)│   └────────┘   └─────────┘
         └────────┘    └─────────┘

External IO:
  - Stripe (Cashier + webhooks)          - verified via Cashier
  - Postmark or SES                      - transactional
  - A separate ESP for marketing         - recommended: Resend/Mailgun
  - Google OAuth, GitHub OAuth           - optional, env-gated
  - IndexNow, Search Console             - SEO
```

**Data flow for a typical request:**

1. CloudFlare terminates TLS → nginx → PHP-FPM.
2. Inertia returns a page prop bundle (not HTML, unless SSR is enabled) → client hydrates React.
3. Writes hit models / services. Cashier operations go through `BillingService` (Redis lock, 35s timeout).
4. Side effects fan out to jobs: `DispatchAnalyticsEvent`, `DispatchWebhookJob`, `PersistAuditLog`, `SubmitIndexNowUrlsJob`.
5. Scheduler (cron → `routes/console.php`) runs lifecycle, scoring, pruning, health-alert commands.

**API contracts:**
- Web: Inertia (typed props, no REST layer needed for internal UI).
- External: Sanctum-token API under `/api/*` for programmatic access.
- Webhooks: incoming at `/api/webhooks/{provider}` (HMAC-verified), outgoing via `DispatchWebhookJob`.

---

## 4. Storage & State Choices

### MySQL (primary store)

**Keep it.** Laravel 12 + MySQL 8 is the lowest-surprise path. A few sharp edges at your scale:

- **Use a managed DB from day 1** (DigitalOcean / Hetzner / Neon). ~$15/mo buys automated backups, point-in-time recovery, patching, and metrics. Running MySQL yourself on the app VPS means your backups are your problem — and untested backups are not backups.
- **Hard deletes are the project default.** Good call. Soft deletes become an admin nightmare at the million-row mark; at your scale they're overkill. The lifecycle / audit subsystems that *do* need history use dedicated tables (`AuditLog`, `UserStageHistory`, `EmailSendLog`).
- **Schema discipline already enforced** (nullable columns, `Schema::hasColumn()` checks, `->constrained()->cascadeOnDelete()`). Don't relax this.

**Trade-off I'd flag:** SQLite-in-tests vs MySQL-in-prod has bitten teams before (JSON columns, functions, collation differences). The project mitigates this by running the CI suite against MySQL 8. Keep that CI leg green.

### Redis (cache, session, locks, queue)

Redis carries a lot of state here:
- Cache (admin dashboard stats, billing tier distribution, feature-flag overrides)
- Session (default Laravel driver, often Redis in prod)
- Redis locks (35s, required for Cashier mutations)
- Queue backend (assumed)

**The operational risk:** if Redis disappears, you lose in-flight subscription locks, session state, and queue contents. Two sane options at your scale:

1. **Managed Redis** (~$15/mo Upstash / managed DO / managed Hetzner). Pick this if billing is enabled.
2. **Co-locate Redis on the app VPS + persistence (AOF).** Cheaper; acceptable if you're not selling to enterprises.

Do *not* split Redis across boxes. One instance, backed up.

### File storage

`config/filesystems.php` is standard Laravel. At 1k users, S3-compatible (Backblaze B2, R2, DO Spaces) is the right pick once you have user uploads. Until then, `local` driver is fine.

---

## 5. Jobs, Queues, Scheduling

### Current state

- 6 job classes (`DispatchAnalyticsEvent`, `DispatchWebhookJob`, `PersistAuditLog`, `SubmitIndexNowUrlsJob`, `CancelOrphanedStripeSubscription`, `BroadcastAnnouncementJob`).
- 16 scheduled commands (lifecycle emails, score computation, pruning, health alerts, trial expiry, dunning).
- `composer dev` runs `queue:listen` in foreground for local.
- Horizon is suggested but not required.

### Recommendation

- **Single queue, single worker (supervisor-managed) at this scale.** One process: `php artisan queue:work --tries=3 --backoff=60 --sleep=3`. Horizon is lovely but a second moving part; skip until you actually need queue priorities.
- **Turn on Horizon the day you split queues.** That's the trigger, not a vague "when it grows."
- **Schedule commands idempotently.** Most already are via `EmailSendLog` dedup, but verify: it's safe to run `lifecycle:send-dunning` twice in a row if cron flaps.
- **Prune religiously.** `audit:prune`, `webhooks:prune-stale`, `prune-read-notifications`. Cron all three daily. Unbounded tables are the #1 slow-burn outage on a VPS.
- **Alert on queue depth.** One metric to monitor: "jobs in queue > N for > 5 min." This catches everything from a stuck worker to a runaway loop. Without it, a broken worker is invisible until users complain.

---

## 6. Observability & On-Call (solo operator's actual job)

This is where the starter has the widest gap for your context. It has pieces; it doesn't have a runbook.

| Need | Current | Recommendation |
|---|---|---|
| Error tracking | Sentry wired | Keep. Free tier = 5k events/mo, sufficient. |
| Uptime | `/up` + `/health` endpoints | Add BetterStack / Uptime Kuma. Alert to phone (SMS or push), not email. |
| Log aggregation | Pail (dev only), Laravel log file | Ship logs to CloudWatch / BetterStack / Axiom. Don't SSH to grep under pressure. |
| Metrics | None obvious | At 1k users you don't need Prometheus. One StatusCake-style dashboard covers it. |
| Alerting policy | Not defined | Write *one* page: "who gets paged, for what, through what channel." It's you, so keep it honest. |
| Health-check command | `admin:health-alert` exists | Wire this to cron every 5 min. Output → your alerting channel. |

**Rule of thumb:** the phone should ring only for "users can't log in," "payments are failing," and "site is down." Everything else is a Monday-morning email.

---

## 7. Deployment & Release

Current design: VPS + `deploy/` nginx + supervisor configs + `scripts/` setup. No Docker. This is appropriate.

**Gaps for a solo operator:**

- **Zero-downtime deploys.** Laravel's Envoyer, Deployer.org, or even a simple `rsync + symlink + php-fpm reload` script. The project has `deploy.sh` — verify it atomically swaps releases rather than deploying in place. If it doesn't, a bad deploy mid-request is a 500-storm.
- **Migration safety.** The `.claude/rules/migrations.md` already enforces nullable + two-phase for destructive changes. Two-phase is heavy for a solo op, but the nullable rule is what saves you.
- **Rollback.** Keep 3 previous releases on disk; `current` symlink makes rollback a 10-second operation.
- **Backup verification.** `scripts/vps-verify.sh` exists. Add a *monthly* cron that restores yesterday's DB backup to a scratch DB and runs one read query. A backup you haven't restored is a coin flip.
- **TLS.** Use CloudFlare in front (free, full TLS) + Let's Encrypt at origin. Auto-renewal via certbot cron.

---

## 8. Scale Headroom (what works, what to revisit, when)

At your 1k-user envelope, none of the following are urgent. But knowing the triggers saves a rewrite later.

| Trigger | Action |
|---|---|
| First user asks for teams / orgs | Stop. Do tenancy deliberately (`stancl/tenancy` or add account scope). Don't retrofit. |
| Queue depth alerts firing during marketing sends | Split queues (`mail`, `webhooks`, `default`), add workers, install Horizon. |
| Admin dashboard page load > 2s | Add MySQL read replica; send admin reads there. |
| `audit_logs` table > 10M rows | Partition by month, or move to a columnar store (ClickHouse). Not before. |
| SEO traffic plateau despite ranking URLs | Turn on `INERTIA_SSR_ENABLED`, run `bootstrap/ssr/ssr.mjs` under PM2. The SEO shell is a fallback. |
| Stripe events lost during an outage | Implement webhook replay from the Stripe dashboard. Already most of the way there via `IncomingWebhook`. |
| Support burden > 5 hrs/week | Add self-serve account tools (close account, export data, change email) — currently mostly admin-only. |

---

## 9. Cost & Build-vs-Buy Trade-offs

Bias: a solo operator should buy everything where the all-in cost is < 1 hour of your time per month.

| Area | Recommendation |
|---|---|
| Hosting | Hetzner CX22 (~$5/mo) or DO $12 droplet. Don't over-provision. |
| DB | Managed MySQL / Postgres ($15/mo). **Do not self-host for prod.** |
| Redis | Managed ($10–15/mo) if billing is on; co-located with AOF if not. |
| Transactional email | Postmark ($15/mo, best deliverability) or Resend. SES if you're cost-sensitive and have SPF/DKIM dialed. |
| Marketing email | Separate ESP. Mixing tx + marketing on one sender kills deliverability. |
| Error tracking | Sentry free tier. |
| Uptime | BetterStack free (10 monitors, 3 min). |
| Analytics | Plausible ($9/mo) or self-host. PostHog cloud if you need funnel analysis. |
| CDN | CloudFlare free. |
| Logs | Axiom free tier (0.5 GB/mo, usually plenty). |
| DNS | CloudFlare free. |
| Status page | BetterStack bundles it. Or a free `status.yoursite.com` page. |

Rough all-in per product: **$35–75/mo** including managed DB, Redis, email, and monitoring.

---

## 10. Trade-off Analysis (the explicit list)

Every non-obvious choice this template makes, with the trade-off and my call for your context:

| Decision | Trade-off | Recommendation |
|---|---|---|
| Single-tenant | Simpler code; blocks B2B teams | Keep. Reverse only with a plan. |
| VPS + no Docker | Simple ops; no autoscale | Keep at this scale. |
| Feature flags in `config/` | Boot-time flags (not per-request) = can't A/B test | Accept. Use Unleash / PostHog flags if you need per-user toggles later. |
| Inertia + React | No API layer = faster; but client is JS-heavy for public pages | Wire up SSR when SEO matters. |
| Hard deletes default | Simpler DB, cheaper storage | Keep. Use audit tables where history matters. |
| Mutation testing in CI | Slow CI, high bar | Move to weekly cron, not every PR. |
| 11 feature flags | Powerful; also 11 things to test every combo of | Enable only what you need in prod. Resist "ship dark" sprawl. |
| Laravel Cashier | Battle-tested; tightly coupled to Stripe | Don't abstract. If you need another PSP later, rewrite cleanly. |
| Sanctum (not Passport) | Simpler; no OAuth2 server | Keep. You are not Auth0. |
| Ziggy (routes in JS) | Nice DX; 50kb bundle | Accept. |
| Custom admin vs. Filament/Nova | More code; fits you precisely | Keep if it's serving you; reconsider only if admin work dominates. |
| Rich public SEO surface (/pricing, /blog, /compare/*, /guides/*) | Great for solo marketing; harder to SSR correctly | Wire SSR before launching marketing campaigns. |

---

## 11. What I'd Do in the First Week

Concrete, ranked, small.

1. **Audit enabled feature flags.** Default to the minimum set for the product you're actually shipping. You can always turn more on. Leaving everything on means 3x the error surface and test matrix.
2. **Put CloudFlare in front.** Free, 15 minutes, gets you TLS, WAF, DDoS, origin caching.
3. **Managed DB.** Move MySQL off the app VPS. Back up nightly, test restore monthly.
4. **Set up alerting.** BetterStack on `/health` every 3 min → SMS. `admin:health-alert` cron every 5 min → same channel.
5. **Wire the scheduler.** Every lifecycle / prune command needs a cron entry. Missing these for a few months turns into a "where did all my disk go" mystery.
6. **Pick a transactional ESP + verify SPF/DKIM/DMARC.** Before you send any lifecycle email to a real user.
7. **Write `ops/RUNBOOK.md`.** One page. What do you do when: payment webhook fails, queue stalls, DB disk is 90%, a user email bounces forever, Stripe is down. Future-you at 2 a.m. will thank present-you.
8. **Clean repo root.** 150+ `AGENT_REVIEW_*.md` / `IMPLEMENTATION_REPORT_*.md` / audit JSONs in the root. Move to `storage/agent-reports/` or `.gitignore` them. They're making every `ls` painful and will confuse an agent doing codebase search.

---

## 12. What I'd Revisit As the System Grows

- **At ~100 paying users:** real contracts — DPA, privacy policy review, basic SOC2-lite posture. The `compliance-tracking` skill helps, but the work is human.
- **At ~500 users:** consider a read replica; start segmenting queues; add structured logging with a request ID so you can trace a single user's incident.
- **At ~1,000 users (your ceiling):** if you're thinking of going past, the next decision is architectural: multi-tenant, horizontal workers, or a clean split of marketing site vs. app. Don't let it happen by accident.

---

## 13. Assumptions I'm Making (call these out if wrong)

- You're deploying one product on this template, not N products sharing infra.
- You're comfortable with Laravel in prod and nginx + supervisor.
- You're open to paid managed services in the $10–20/mo range.
- Marketing / SEO matters (the template has a huge public surface suggesting yes).
- Billing will be enabled for the paid products; free apps may disable it.
- You want to keep agent-assisted development as your primary leverage — the AI-safeguards docs are a feature, not overhead.

---

## 14. Summary Verdict

This template is **over-equipped for 1k users** — which is the right problem to have. Most starter kits are under-equipped and leave you reaching for Stripe webhook verification and lifecycle email infra six months in. This one ships with them.

The work for a sole operator is not to add to it. The work is: **turn off what you don't need, wire monitoring for what you kept, and outsource the things a pager would wake you up for.** If you do those three things in the first week, the template carries you comfortably to your scale ceiling.

---
*Generated 2026-04-22. Revisit this doc quarterly or when you cross a scale trigger in §8.*
