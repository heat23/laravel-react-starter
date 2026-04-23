# Feature Flag Audit — Sole-Operator, Up to ~1k Users

**Type:** Read-only audit. No code changes made.
**Sources consulted:** `config/features.php`, `.env.example`, `phpunit.xml`, `docs/FEATURE_FLAGS.md`, `app/Helpers/features.php`, `app/Services/FeatureFlagService.php` (via `HandleInertiaRequests` usage), route files, and 49 PHP + 13 TSX call sites.

---

## 1. The 12 Flags, at a Glance

| # | Flag | Config default | `.env.example` | `phpunit.xml` | Usage count (PHP/TSX) |
|---|---|---|---|---|---|
| 1 | `billing.enabled` | **false** | false | **true** | 2 |
| 2 | `social_auth.enabled` | **true** | **false** | true | 6 |
| 3 | `email_verification.enabled` | true | true | true | 2 |
| 4 | `api_tokens.enabled` | true | true | true | 4 |
| 5 | `user_settings.enabled` | true | true | true | 2 |
| 6 | `notifications.enabled` | false | false | (unset, default false) | 3 |
| 7 | `onboarding.enabled` | **true** | **false** | true | 1 |
| 8 | `api_docs.enabled` | false | false | (unset, default false) | 1 |
| 9 | `two_factor.enabled` | false | false | **true** | 2 |
| 10 | `webhooks.enabled` | false | false | **true** | 3 |
| 11 | `admin.enabled` | **false** | false | **true** | 7 (boot-time gate on `routes/admin.php`) |
| 12 | `indexnow.enabled` | false | false | true | 1 |

Plus two nested billing flags:
- `billing.coming_soon` — soft-launch pricing page before payments work.
- `billing.tax_enabled` — Stripe Tax. **Destructive if mis-enabled.** Must match your Stripe Tax registration settings or you'll collect wrong tax.

---

## 2. Discrepancies I'd Fix Before Launch

These are real, not cosmetic.

### 2a. Three-way drift between config, env example, and test env

```
config/features.php      .env.example           phpunit.xml
social_auth: true   ←→   FEATURE_SOCIAL_AUTH=false   ←→   true
onboarding: true    ←→   FEATURE_ONBOARDING=false    ←→   true
```

- A developer copying `.env.example` gets the opposite of what `config/features.php` claims are defaults for `social_auth` and `onboarding`. In both cases the env var wins at runtime, so **the hard-coded `true` defaults in `config/features.php` are dead code when `.env.example` is used verbatim.**
- Pick one source of truth. My suggestion: let `.env.example` be *the* documented production default, and align `config/features.php` fallbacks to match. Otherwise agents and new devs will keep getting burned.

### 2b. Tests enable a superset of production

`phpunit.xml` forces 10 of 12 flags ON (plus 2 tests-only defaults at OFF). `.env.example` runs with 3 flags ON.

- **Risk:** The test suite exercises code paths that the `.env.example`-derived production never hits. Conversely, broken fall-throughs that only happen when a feature is OFF are never tested.
- **Confirmed by** `.claude/rules/testing.md`: "Routes conditionally registered at boot time cannot be tested for both enabled/disabled states in the same test suite… Feature flags set in `phpunit.xml` determine which routes are registered at application boot."
- **Practical implication:** after you pick your prod flag set (see §4), run a one-off test pass with `phpunit.xml` aligned to that set to catch regressions. The current "tests run with everything on" baseline is a test-coverage lie for any stripped-down deployment.

### 2c. `admin.enabled=false` by default is wrong for a sole operator

- Admin is where you impersonate users to debug billing, read audit logs, flush caches, review feature-flag overrides, see failed jobs. It *is* your console.
- Leaving it off by default makes sense for a generic OSS starter (security-by-default). For your use case, it should be on from day 1, behind the existing `admin` middleware.
- The `.claude/rules/` and CLAUDE.md both call admin "feature-gated" — correct — but there's no reason a solo op should ship without it. (And the `FeatureFlagService` has a hard floor that prevents DB overrides from enabling admin when env is false, which is the right defense.)

### 2d. `social_auth.enabled=true` in config is a confusing default

- The config default is `true`, but the helper comment admits it's safe because "no buttons render unless `GOOGLE_CLIENT_ID` / `GITHUB_CLIENT_ID` are actually set."
- That's fine runtime behavior, but `.env.example` sets `FEATURE_SOCIAL_AUTH=false`, so the "safe default" is never the operative default for new deployments. This is just noise. Pick one.

### 2e. `onboarding` silently depends on `user_settings`

From `docs/FEATURE_FLAGS.md`:
> `onboarding` stores completion state in `user_settings` table… disabling `user_settings` silently drops onboarding completion tracking.

- If you ever turn off `user_settings` while `onboarding` stays on, users will be shown the wizard on every login. There's no assertion that blocks this misconfiguration. A runtime check in `FeatureFlagService::resolve()` (return false when hard dependency is off) would kill a class of bugs cheaply. **Not fixing here (read-only), but worth a follow-up.**

### 2f. `billing.tax_enabled` deserves a louder guardrail

- Flipping this to `true` immediately passes `automatic_tax: { enabled: true }` to every new subscription in `BillingService`.
- If your Stripe Tax registration isn't set up for the jurisdictions you're selling into, you'll either over-collect (bad, refundable) or under-collect (bad, not refundable).
- Low-risk treatment: leave it off until a lawyer or accountant signs off. This is *not* a technical decision.

---

## 3. Hard-Dependency Map (confirmed from `docs/FEATURE_FLAGS.md` + code)

```
onboarding  ──requires──▶  user_settings
billing     ──requires──▶  webhooks        (auto-enabled in routes/api.php for Stripe)
two_factor  ──soft──────▶  user_settings   (fallback exists)
api_docs    ──requires──▶  api_tokens
admin       ── protected: cannot be DB-overridden when env=false (good)
notifications ──soft──▶  webhooks          (webhook failures log to DB if notifications off)
billing     ──soft──▶    email_verification
social_auth ──soft──▶    email_verification  (OAuth users pre-verified)
```

No circular dependencies. All runtime-enforceable via `FeatureFlagService::resolve()`, but currently not enforced — dependencies are documented, not coded.

---

## 4. Recommended Flag Sets by SaaS Archetype

For each, I'm naming the flag state for a 1k-user ceiling. ✅ = on, ⬜ = off, ⚠️ = conditional.

### 4a. Paid SaaS (your stated default — billing on, marketing surface on)

| Flag | State | Rationale |
|---|---|---|
| `billing` | ✅ | You're charging. |
| `webhooks` | ✅ | Hard dep of billing; plus customer-facing integrations are a differentiator. |
| `email_verification` | ✅ | Reduces fraud on free trials; required before `billing` subscriptions. |
| `user_settings` | ✅ | Hard dep of `onboarding`, soft dep of `two_factor`. Also cheap. |
| `onboarding` | ✅ | Activation is the #1 leverage point at this scale. |
| `two_factor` | ✅ | If you handle money or integrations, don't skip. Friction is low (TOTP, opt-in). |
| `api_tokens` | ⚠️ | On only if your product has programmatic users. Otherwise off — removes token management UI, fewer attack-surface questions. |
| `api_docs` | ⚠️ | On only if `api_tokens` is on and you want a public API. Otherwise off (Scribe is a dev dep — no prod cost). |
| `admin` | ✅ | Your console. Non-negotiable. |
| `social_auth` | ⚠️ | On only if you've configured `GOOGLE_CLIENT_ID` or `GITHUB_CLIENT_ID`. Otherwise off — don't render a dead feature. |
| `notifications` | ⬜ | Unless you need in-app bell. Email covers most use cases at this scale. Turn on if/when you need real-time UI signals. |
| `indexnow` | ⚠️ | On only if public SEO matters (you have a marketing surface). Also set `INDEXNOW_AUTO_PING_SITEMAP=true` or it does nothing automatic. |

**Net:** 7–9 flags on, 3–5 off. Concentrates your test matrix on what you actually ship.

### 4b. Free internal tool / side-project

| Flag | State |
|---|---|
| `email_verification` | ✅ |
| `user_settings` | ✅ |
| `two_factor` | ✅ |
| `api_tokens` | ⚠️ (on if you're automating anything) |
| `admin` | ✅ |
| Everything else | ⬜ |

### 4c. Pure MVP / landing page + signup

| Flag | State |
|---|---|
| `email_verification` | ✅ |
| `user_settings` | ✅ (cheap; forward-compatible) |
| `admin` | ✅ |
| Everything else | ⬜ |

---

## 5. What Each "Off" Flag Actually Removes (audit of impact)

Useful if you're on the fence about any single flag. Counts are from the grep of 49 PHP + 13 TSX sites.

- **`billing` off:** No `/pricing`, `/billing`, `/buy/*` routes. `SubscriptionController` routes gone. Cashier still in composer (disk cost only), but no Stripe calls. Inertia `auth.subscription` prop returns `null`. Lifecycle email commands (`send-dunning`, `send-trial-*`) are no-ops without subscriptions to target.
- **`webhooks` off:** No webhook endpoints UI, no incoming webhook routes (except Stripe, which is handled by Cashier independently). `DispatchWebhookJob` is dead code but harmless.
- **`social_auth` off:** No `/auth/social/*` routes, no OAuth buttons, no `SocialAccount` creation. Cleaner login page.
- **`email_verification` off:** `verified` middleware stops firing. All "please verify" redirects skipped. **Caution:** some lifecycle email commands assume verified users — review if you turn this off.
- **`api_tokens` off:** No `/settings/tokens` route, no `/api/user/tokens` API, no Inertia prop for token management. Sanctum still works for session-based `/api/*` calls.
- **`user_settings` off:** Theme/timezone not persisted (users get defaults every session). Onboarding and two-factor have soft dependencies.
- **`notifications` off:** No bell icon, no unread count, no notification API endpoints. Critical webhook / billing notifications still fire via email.
- **`onboarding` off:** New users land directly on the dashboard. `EnsureOnboardingCompleted` middleware becomes a passthrough. Useful for API-only / headless deployments.
- **`api_docs` off:** `/docs` returns 404. Scribe is a dev dep so no prod impact from the package itself.
- **`two_factor` off:** Removes `/settings/two-factor`, `/two-factor/challenge`. User model trait is still loaded but unused.
- **`admin` off:** `routes/admin.php` never loaded. Entire admin namespace dark. **You lose your debugging surface — do not ship with this off unless the product is read-only / truly stateless.**
- **`indexnow` off:** No search-engine ping on content publishes. Bing/Yandex will still find you eventually; they'll just be slower.

---

## 6. Runtime vs. Boot-Time Gating (current assignment)

Per `docs/FEATURE_FLAGS.md` and confirmed in routes/controllers:

**Boot-time (route never registered when off — returns 404):**
- `admin` (routes/web.php:232 → routes/admin.php)
- `billing` (routes/web.php:152)
- `api_tokens` (routes/web.php:145, routes/api.php:59)
- `email_verification` (routes/web.php:120, used to build middleware list)
- `indexnow` (routes/web.php:222)
- `user_settings` (routes/api.php:43)

**Runtime (route always registered; controller calls `abort_unless(feature_enabled(...))`):**
- `social_auth`
- `two_factor`
- `webhooks`
- `notifications`

**Assignment looks right.** Runtime gating only pays off when you want per-user DB overrides, which matches the "user-opt-in or staged-rollout" features. No obvious miscategorizations. One tiny nit: `onboarding` is gated via middleware (`EnsureOnboardingCompleted`) rather than route registration — documented correctly in CLAUDE.md.

---

## 7. Inertia Shared Props: Feature Flag Payload

Every request (authenticated or guest) receives all 12 flag values as JSON in `props.features`. Confirmed in `HandleInertiaRequests` lines 188–200. Payload size ≈200 bytes after gzip — fine.

Good practices already in place:
- Server-side resolution via `FeatureFlagService::resolveAll($user)` respects per-user overrides.
- Conditional props (`auth.subscription`, `limit_warnings`, `pql_threshold`, `notifications_unread_count`) only evaluate their closures when the feature is on — zero cost when off.

Nothing to fix here.

---

## 8. TSX Frontend Gating

13 usages in 5 files. Mostly in `DashboardLayout.tsx`, `AppLayout.tsx`, and a couple of `Pages/Guides/*`. No runaway duplication.

- `FeatureFlagsGuide.tsx` and `TwoFactorGuide.tsx` are public marketing pages that reference flags to show "if enabled, do X" language. Low risk — they're static explanatory content.
- Layouts read `features.admin`, `features.twoFactor`, `features.notifications` to decide which nav links to show. Correct pattern.

**One thing to watch:** when you turn a flag off in prod, rebuild the SPA. The TSX bundles don't recompile on env change; they read the `features` prop at runtime, so the nav will hide/show correctly — but any inline `import()` guarded only by the flag will still ship bytes for the off feature. At 1k users, bundle size is not a pain point, so defer.

---

## 9. Prioritized Findings

Sorted by what would burn you first.

| # | Finding | Severity | Why |
|---|---|---|---|
| 1 | `.env.example` and `config/features.php` disagree on `social_auth` and `onboarding` defaults | High | Whichever a new deploy picks, the *other* is wrong and confusing. |
| 2 | `admin` off by default makes the product nearly undebuggable in prod | High | You are the on-call. Without admin, you're SSH-ing and tinkering. |
| 3 | `phpunit.xml` enables a superset of prod → silent test-coverage gaps for stripped-down deploys | Medium | Only matters once you ship with a reduced flag set (which is the recommendation). |
| 4 | `billing.tax_enabled` has no guardrail beyond a comment | Medium | Flipping it wrong is a compliance mistake, not a bug. |
| 5 | Hard dependencies documented but not runtime-enforced | Medium | E.g., `onboarding` on + `user_settings` off loops the wizard. Easy to add a check. |
| 6 | `social_auth.enabled=true` in config is effectively dead code because `.env.example` sets it false | Low | Just noise, but confusing to new contributors. |
| 7 | `indexnow.auto_ping_sitemap=false` default means enabling the flag is a no-op without a second env var | Low | At least the default is "off"; easy to miss when you want it on. |
| 8 | `api_docs` and `api_tokens` linked only by documentation (`api_docs` requires `api_tokens`) | Low | Scribe crawls routes regardless, so `api_docs=on` + `api_tokens=off` produces misleading docs, not a crash. |

---

## 10. Recommended Next Actions (if you want to follow up)

I'm not making changes. When you're ready:

1. **Pick your archetype from §4** and update `.env.example` to match, then align `config/features.php` fallbacks + `phpunit.xml` to the same set.
2. **Flip `FEATURE_ADMIN=true` in `.env.example`.** This is a sole-operator starter, not a locked-down enterprise OSS library.
3. **Add a one-line assertion** in `FeatureFlagService::resolve()`: if a hard dependency is off, return false and log a warning. Prevents the "silent broken" class of bugs from §2e.
4. **Document the tax-enabled guardrail** as a checklist item in `docs/IMPLEMENTATION_GUARDRAILS.md`, not just a code comment.
5. **Run `php artisan test --filter=Feature` with a minimal flag set** once, to surface any tests that depend on flags being on.
6. **Delete or consolidate** any of the 3 dead defaults surfaced in §2a / §2d / §6.

---

## 11. Assumptions I Made

- "Sole operator, up to 1k users" means cognitive load dominates over scale — bias toward fewer flags on.
- You want billing, onboarding, and admin on for paid SaaS products (stated in the previous design review).
- You'll ship each SaaS with a different flag set from the same template, not one deployment running everything.
- Test coverage matters to you (you have Pest + mutation testing + CI gates already).

---

*Generated 2026-04-22. Read-only audit. No code changes.*
