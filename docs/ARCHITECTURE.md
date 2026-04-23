# Architecture — Laravel React Starter

**Living document.** Last updated: 2026-04-23.
Supersedes `architecture-review-sole-operator.md`, `SYSTEM_DESIGN_SOLO_OPERATOR.md`, and `architecture-removal-plan.md`.

---

## Optimizing for a sole operator (0–1,000 users per product)

| Dimension | Target |
|-----------|--------|
| Users per product | 0–1,000 (indie / lifestyle) |
| Operator count | 1 |
| Uptime goal | ~99.5% |
| Cost ceiling | $35–75/mo all-in |
| Time to ship | Hours, not days |
| Recovery time | Minutes, not hours |

Bias every decision toward **managed services, narrow feature surface, and reversible choices**.

---

## System topology

```
CloudFlare (TLS + WAF + caching)
    │
Nginx (VPS — gzip, static cache, trusted-proxy headers)
    │
    ├── PHP-FPM / Laravel
    ├── Queue Worker (supervisor, single queue)
    └── Scheduler (cron → routes/console.php)
         │
         ├── MySQL 8 (managed — automated backups, PITR)
         ├── Redis (cache + locks + session + queue)
         ├── Stripe (Cashier + webhooks)
         ├── Postmark / SES (transactional email)
         └── Sentry (error tracking)
```

**Inertia data flow:**
1. CloudFlare → nginx → PHP-FPM.
2. Inertia returns a page prop bundle; client hydrates React.
3. Writes hit models/services. Cashier mutations go through `BillingService` (Redis lock, 35s timeout).
4. Side effects fan out to jobs: `PersistAuditLog`, `DispatchWebhookJob`, `SubmitIndexNowUrlsJob`.
5. Scheduler runs lifecycle email, prune, and health-alert commands.

---

## What to keep vs defer vs not build

### Keep (earns its spot for a sole op)

- **Billing (`BillingService` + Redis locks + Cashier)** — Redis-locked mutations and Cashier eager-load pattern. The `billing.md` gotchas doc alone is worth the implementation cost.
- **Auth + 2FA + email verification** — baseline credibility. `laragear/two-factor` is the right call.
- **Sentry + `/health` endpoint** — you cannot operate without visibility. Enable Sentry day 1.
- **Feature flags as a concept** — use fewer of them; default more OFF.
- **Pest + Vitest + Playwright smoke tests** — right-sized test stack.
- **PHPStan + Pint** — cheap, high value.
- **Ziggy + Inertia + Breeze + Radix UI kit** — solid, fast to build on.
- **Single-tenant default** — explicit and documented is correct.

### Defer (flag OFF, add when you have signal)

| Subsystem | Why defer |
|-----------|-----------|
| `webhooks.enabled` | No one integrates with pre-PMF product |
| `notifications.enabled` | In-app notifications only matter once users return regularly |
| `api_tokens.enabled` | Public API is a product surface — deliberate decision |
| `indexnow.enabled` | Requires a product people search for. Month 6+, not day 1 |
| `onboarding.enabled` | Usually needs full rewrite per product; don't over-invest in shared version |

Default-off configuration for a new project fork:
```env
FEATURE_BILLING=true            # if monetizing
FEATURE_EMAIL_VERIFICATION=true
FEATURE_USER_SETTINGS=true
FEATURE_ADMIN=true
FEATURE_TWO_FACTOR=true

FEATURE_SOCIAL_AUTH=false       # add on specific demand
FEATURE_API_TOKENS=false
FEATURE_NOTIFICATIONS=false
FEATURE_ONBOARDING=false
FEATURE_WEBHOOKS=false
FEATURE_INDEXNOW=false
```

### Not in the starter (removed or never added)

These were either removed or are outside scope:

- **Lifecycle/scoring analytics platform** — `EngagementScoringService`, `LeadScoringService`, `CustomerHealthService`, `CohortService`, `ProductAnalyticsService`, UTM capture. Removed. See ADRs 0006–0008.
- **SSR** — removed in favor of Blade SEO shell (no Node process in prod). See `seo.md`.
- **Scribe API docs** — removed. No API consumers pre-PMF.
- **Admin impersonation** — removed. SSH + tinker is faster and safer for sole ops.
- **Infection (mutation testing) in CI** — moved to weekly cron; too slow for every commit.

---

## Storage and state choices

### MySQL (primary)

- Use a managed DB from day 1 (~$15/mo). Self-hosted MySQL backups are untested backups.
- Hard deletes by default. History lives in `audit_logs` and `user_stage_history`.
- Schema discipline enforced: nullable columns, `Schema::hasColumn()` guards, `->constrained()->cascadeOnDelete()`.
- CI runs against MySQL 8 (not just SQLite) to catch JSON, functions, collation differences.

### Redis (cache + locks + session + queue)

Redis carries: admin cache stats, billing locks, sessions, queue. If Redis disappears, in-flight subscription locks and session state are lost.

- Managed Redis (~$10–15/mo Upstash or DO) if billing is enabled.
- Co-located with AOF persistence if cost-constrained and no enterprise customers.
- `/health` should surface Redis connectivity.

### File storage

`local` driver is fine until you have user uploads. Then: S3-compatible (Backblaze B2, R2, or DO Spaces).

---

## Jobs and scheduling

- **Single queue, single worker** at this scale. Horizon only when you actually need queue priorities.
- **Schedule commands idempotently** — `EmailSendLog` dedup prevents double-sends, but commands must be safe to re-run.
- **Prune religiously.** `audit:prune`, `webhooks:mark-abandoned`, `webhooks:delete-old`, `prune-read-notifications` — run daily. Unbounded tables cause VPS disk growth.
- **Alert on queue depth.** If jobs > N for > 5 min, something is broken invisibly.

---

## Observability

| Need | Tool | Notes |
|------|------|-------|
| Error tracking | Sentry | Free tier = 5k events/mo |
| Uptime | BetterStack / Uptime Kuma | Alert to phone (SMS), not email |
| Logs | Axiom / BetterStack | Ship logs off-box — don't grep under pressure |
| Health command | `admin:health-alert` | Cron every 5 min → alerting channel |

**Alert policy:** Phone should ring for: users can't log in, payments failing, site down. Everything else = Monday email.

---

## Deployment

- Zero-downtime: atomic symlink swap (Envoyer, Deployer.org, or rsync + symlink script).
- Keep 3 previous releases on disk for fast rollback.
- Monthly: restore yesterday's backup to a scratch DB and run a read query. Untested backup = no backup.
- TLS: CloudFlare (free) + Let's Encrypt at origin with auto-renewal.
- Post-deploy: `php artisan queue:restart` + `php artisan optimize:clear`.

---

## Scale headroom

| Trigger | Action |
|---------|--------|
| First user asks for teams/orgs | Stop. Do tenancy deliberately (`stancl/tenancy`). Don't retrofit. |
| Queue depth alerts during marketing sends | Split queues, add workers, install Horizon |
| Admin dashboard > 2s page load | Add MySQL read replica |
| `audit_logs` > 10M rows | Partition by month or move to columnar store |
| Support burden > 5 hrs/week | Add self-serve account tools (close, export, change email) |

---

## Trade-off register

| Decision | Trade-off | Recommendation |
|----------|-----------|----------------|
| Single-tenant | Simpler code; blocks B2B teams | Keep. Reverse only with a plan. |
| VPS + no Docker | Simple ops; no autoscale | Keep at this scale. |
| Boot-time feature flags | Can't A/B test per-user | Accept. Use PostHog flags if needed later. |
| Inertia + React (no SSR) | Fast build; JS-heavy for public pages | Blade SEO shell as crawler fallback. |
| Hard deletes default | Simpler DB; cheaper storage | Keep. Audit tables where history matters. |
| Sanctum (not Passport) | Simpler; no OAuth2 server | Keep. You are not Auth0. |
| Custom admin vs Filament/Nova | More code; fits precisely | Keep if it's serving you. |
| `EmailSendLog` dedup | Shared between lifecycle and billing dunning | Keep — removal would break billing dedup. |

---

## Tenancy note

Single-tenant is pinned in CLAUDE.md. ~70% of B2B SaaS ideas eventually need teams. Retrofitting `tenant_id` across 16 models mid-product is a multi-week job done under pressure.

When starting a new project: make a conscious day-1 call. If "maybe teams someday," add `team_id` nullable FK to `users` and scope queries through a `CurrentTeam` helper from the start. Costs days; saves weeks.
