# Content Calendar — Laravel React Starter

15-topic content queue for blog and guide production. Priority ordered by estimated search volume and funnel fit.

## Published

| Slug | Title | Published | Type |
|------|-------|-----------|------|
| `laravel-redis-locks-stripe-billing` | How We Use Redis Locks to Prevent Double-Charges in Laravel Stripe Billing | 2026-03-10 | Blog |
| `inertia-react-typescript-admin-panel` | Building a Type-Safe Admin Panel with Inertia.js, React, and TypeScript | 2026-02-28 | Blog |
| `laravel-feature-flags-without-launchdarkly` | Laravel Feature Flags Without LaunchDarkly — The Env + Database Pattern | 2026-02-15 | Blog |

## Queue (priority order)

### High Priority — Bottom of Funnel (BoFu)

1. **"Laravel SaaS boilerplate vs building from scratch — true cost breakdown"**
   - Target: devs evaluating buy vs build
   - Angle: concrete hour estimates per feature (auth 40h, billing 80h, admin 60h...)
   - CTA: Link to /guides/cost-of-building-saas-from-scratch
   - Format: Blog post + data table

2. **"Pest test patterns for Laravel Cashier — how we test Stripe billing"**
   - Target: Laravel devs already evaluating this kit
   - Angle: show the actual test code — faking Stripe, testing race conditions
   - CTA: Link to /features/billing
   - Format: Blog post with code

3. **"Laravel Inertia SSR vs Next.js — real world comparison for SaaS teams"**
   - Target: Next.js devs considering Laravel
   - Angle: bundle sizes, TTFB, DX, deployment complexity
   - CTA: Link to /compare/laravel-vs-nextjs
   - Format: Blog post

### Medium Priority — Middle of Funnel (MoFu)

4. **"TOTP two-factor auth in Laravel 12 — implementation walkthrough"**
   - Target: devs adding 2FA to existing Laravel apps
   - Angle: laragear/two-factor step by step with tests
   - CTA: Link to /features/two-factor-auth + /guides/laravel-two-factor-authentication
   - Format: Blog post

5. **"Laravel webhook signing — how HMAC-SHA256 verification works"**
   - Target: devs building integrations
   - Angle: explain HMAC, show the verify pattern, both incoming and outgoing
   - CTA: Link to /features/webhooks
   - Format: Blog post

6. **"Inertia.js shared props — the right way to pass auth and feature flags"**
   - Target: Inertia developers
   - Angle: HandleInertiaRequests middleware patterns, what to share vs what to pass per-page
   - CTA: Link to kit homepage
   - Format: Blog post

7. **"Laravel single-tenant SaaS architecture — what you actually need at early stage"**
   - Target: founders deciding on architecture
   - Angle: why multi-tenant is premature, what single-tenant actually costs you later
   - CTA: Link to /guides/single-tenant-vs-multi-tenant-saas
   - Format: Blog post

### Lower Priority — Top of Funnel (ToFu)

8. **"5 Laravel admin panels compared — Filament, Backpack, Nova, Voyager, custom React"**
   - Target: Laravel devs evaluating admin options
   - Angle: feature matrix + honest pros/cons per option
   - CTA: Link to /features/admin-panel
   - Format: Comparison post

9. **"Laravel 12 new features for SaaS developers — what actually matters"**
   - Target: Laravel devs upgrading
   - Angle: focus on breaking changes and features relevant to SaaS (not framework internals)
   - CTA: Link to starter kit
   - Format: Blog post

10. **"PHP vs Node.js for SaaS in 2026 — an honest developer's take"**
    - Target: full-stack devs choosing a stack
    - Angle: hiring, ecosystem, performance, deployment — with real numbers
    - CTA: Link to /compare (compare hub)
    - Format: Opinion post + data

11. **"Stripe Cashier vs Paddle vs custom billing — comparison for Laravel SaaS"**
    - Target: devs starting billing implementation
    - Angle: when to use each, hidden costs, real integration complexity
    - CTA: Link to /features/billing
    - Format: Comparison post

12. **"Laravel queue workers in production — supervisor, horizon, and failure handling"**
    - Target: devs moving to production
    - Angle: supervisor config, queue monitoring, failed jobs, Redis setup
    - CTA: Link to kit homepage (includes supervisor config)
    - Format: Tutorial post

13. **"React + Laravel Inertia: passing user permissions to the frontend safely"**
    - Target: Inertia developers
    - Angle: shared props security — what to expose vs what to gate server-side
    - CTA: Link to kit homepage
    - Format: Blog post

14. **"Multi-currency billing in Laravel — Stripe prices and display patterns"**
    - Target: SaaS founders expanding internationally
    - Angle: Stripe price objects, currency detection, display formatting
    - CTA: Link to /features/billing
    - Format: Tutorial post

15. **"Laravel audit logging — tracking user actions for compliance and debugging"**
    - Target: devs building compliance features
    - Angle: what to log, the AuditLog model pattern, pruning strategy
    - CTA: Link to /features/admin-panel
    - Format: Blog post

## Content Guidelines

- **No lorem ipsum** — all posts use real Laravel code from the starter kit codebase
- **Code-first** — every post includes at least one working code snippet
- **Honest comparisons** — comparison posts include genuine trade-offs, not just "us > them"
- **CTA** — every post links to a relevant feature page or guide, not just the homepage
- **Length** — target 800–1500 words; long-form guides live in /guides/, shorter takes in /blog/
