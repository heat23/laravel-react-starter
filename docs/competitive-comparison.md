# Competitive Comparison: Laravel React Starter

How this starter template compares to popular Laravel and React SaaS boilerplates.

## Quick Summary

| Feature | This Template | Laravel Spark | SaaSyKit | Wave | Jetstream | Shipfast | Makerkit |
|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Stack** | Laravel + React + TS | Laravel + Livewire | Laravel + Vue | Laravel + Livewire | Laravel + Livewire/Vue | Next.js | Next.js/Remix |
| **Frontend** | React 18 + TypeScript | Blade/Livewire | Vue 3 | Blade/Livewire | Livewire or Inertia+Vue | React | React |
| **Inertia.js** | Yes | No | No | No | Optional (Vue only) | N/A | N/A |
| **TypeScript** | Full | No | Partial | No | No | Yes | Yes |
| **Tailwind CSS** | v4 | v3 | v3 | v3 | v3 | v3 | v3 |

## Detailed Feature Matrix

### Authentication & Security

| Feature | This Template | Spark | SaaSyKit | Wave | Jetstream | Shipfast | Makerkit |
|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Email/password auth | Yes | Yes | Yes | Yes | Yes | Yes | Yes |
| Social OAuth (Google, GitHub) | Yes (feature-flagged) | No | Yes | No | No | Yes | Yes |
| Two-factor authentication | Yes (TOTP + recovery codes) | No | No | No | Yes | No | Partial |
| Email verification | Yes (feature-flagged) | Yes | Yes | Yes | Yes | No | Yes |
| Rate limiting (per-endpoint) | Yes (12 distinct limits) | Basic | Basic | Basic | Basic | No | Basic |
| Security headers (CSP, HSTS) | Yes (configurable) | No | No | No | No | No | No |
| Request tracing (X-Request-Id) | Yes | No | No | No | No | No | No |
| Session regeneration | Yes | Yes | Yes | Yes | Yes | N/A | N/A |
| Admin impersonation | Yes | No | No | No | No | No | No |

### Billing & Subscriptions

| Feature | This Template | Spark | SaaSyKit | Wave | Jetstream | Shipfast | Makerkit |
|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Stripe integration | Yes (Cashier) | Yes (Cashier) | Yes (Cashier) | Yes (Cashier) | No | Yes (Stripe SDK) | Yes (Stripe SDK) |
| Redis-locked billing mutations | Yes | No | No | No | N/A | No | No |
| Plan tiers (free/pro/team/enterprise) | Yes (4 tiers) | Yes | Yes | Yes | N/A | Yes | Yes |
| Team seats with min/max | Yes (1-50) | Yes | Partial | No | N/A | No | Partial |
| Incomplete payment tracking | Yes (automated reminders) | No | No | No | N/A | No | No |
| Dunning/retry emails | Yes (3-email sequence) | No | No | No | N/A | No | No |
| Feature-flagged billing | Yes (disable entirely) | No | No | No | N/A | No | No |

### Admin Panel

| Feature | This Template | Spark | SaaSyKit | Wave | Jetstream | Shipfast | Makerkit |
|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| User management | Yes | No | Yes | Yes | No | No | Yes |
| User impersonation | Yes | No | No | No | No | No | No |
| Health monitoring | Yes (DB/cache/queue/disk) | No | No | No | No | No | No |
| Audit logs | Yes (with IP/user agent) | No | Partial | No | No | No | No |
| Config viewer | Yes | No | No | No | No | No | No |
| System info | Yes | No | No | No | No | No | No |
| Billing dashboard | Yes (MRR, churn, trials) | No | Partial | No | No | No | Partial |
| Data export (CSV) | Yes | No | No | No | No | No | No |
| Feature flag management | Yes (per-user overrides) | No | No | No | No | No | No |
| Failed job management | Yes | No | No | No | No | No | No |

### Developer Experience

| Feature | This Template | Spark | SaaSyKit | Wave | Jetstream | Shipfast | Makerkit |
|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Feature flags | Yes (11 toggleable) | No | No | No | No | No | Partial |
| Pest tests | Yes | No | Partial | No | Yes | N/A | N/A |
| Vitest (frontend) | Yes | N/A | No | N/A | N/A | Yes | Yes |
| Playwright E2E | Yes | No | No | No | No | No | No |
| PHPStan static analysis | Yes | No | Partial | No | No | N/A | N/A |
| Mutation testing (Infection) | Yes | No | No | No | No | No | No |
| Pre-commit hooks | Yes (Husky) | No | No | No | No | No | No |
| CI/CD pipeline | Yes (GitHub Actions) | No | Partial | No | No | No | Partial |
| API documentation (Scribe) | Yes (feature-flagged) | No | Partial | No | No | No | No |

### Infrastructure

| Feature | This Template | Spark | SaaSyKit | Wave | Jetstream | Shipfast | Makerkit |
|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Webhooks (incoming + outgoing) | Yes (HMAC signed) | No | No | No | No | No | No |
| API tokens (Sanctum) | Yes (feature-flagged) | No | Yes | No | Yes | No | No |
| Notification system | Yes (database + email) | No | Yes | Yes | No | No | Partial |
| Onboarding wizard | Yes (feature-flagged) | No | Partial | No | No | Partial | Yes |
| User settings (theme/timezone) | Yes | No | Partial | Partial | No | No | Partial |
| Deployment configs | Yes (nginx, supervisor) | No | Docker | No | No | Vercel | Vercel |

## Key Differentiators

### 1. React + TypeScript + Inertia.js

Most Laravel starters use Livewire (Spark, Wave) or Vue (SaaSyKit, Jetstream). This template is purpose-built for teams who want React with full TypeScript coverage and Inertia.js for seamless SPA behavior without a separate API layer.

### 2. Toggleable Feature Flags

11 feature flags let you enable exactly what you need. Disable billing for internal tools, skip social auth for B2B, or turn off onboarding for developer-facing products. Routes, middleware, and UI conditionally activate — disabled features have zero runtime cost.

### 3. Redis-Locked Billing Mutations

Most starters call Cashier methods directly, risking race conditions when users double-click or concurrent webhook processing overlaps with user actions. This template wraps all subscription mutations in Redis locks with 35-second timeouts, preventing duplicate charges and subscription state corruption.

### 4. Comprehensive Testing

5 testing tools working together: Pest (PHP unit/feature), Vitest (React components), Playwright (E2E), PHPStan (static analysis), and Infection (mutation testing). Pre-commit hooks and CI pipeline enforce quality gates before code ships.

### 5. Production-Grade Admin Panel

Not just user CRUD — the admin panel includes health monitoring, audit logs with IP tracking, billing analytics (MRR, churn, trial conversion), data export, impersonation, failed job management, feature flag overrides, and config viewing. Purpose-built for operating a SaaS, not just building one.

## Pricing Comparison

| Template | License | Price | Updates |
|----------|---------|-------|---------|
| This Template | Open Source | Free | Community |
| Laravel Spark | Commercial | $99/project | 1 year |
| SaaSyKit | Commercial | $149-299 | Lifetime |
| Wave | Open Source | Free | Community |
| Jetstream | Open Source | Free | Official Laravel |
| Shipfast | Commercial | $199 | Lifetime |
| Makerkit | Commercial | $299/year | Subscription |

## When to Choose This Template

**Choose this template if you:**
- Want React + TypeScript (not Livewire or Vue)
- Need a modular starter where features can be toggled off
- Care about billing safety (Redis locks prevent race conditions)
- Want a production-ready admin panel from day one
- Value comprehensive testing (5 testing tools)
- Plan to deploy to a VPS (not serverless)

**Choose a competitor if you:**
- Prefer Livewire (→ Spark or Wave)
- Want Vue.js (→ SaaSyKit or Jetstream)
- Need serverless/edge deployment (→ Shipfast or Makerkit)
- Want an established commercial product with paid support (→ Spark or SaaSyKit)
