/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

NOTE: These are GTM (Go-to-Market) strategy recommendations, not code bugs. They involve content creation, marketing infrastructure, and product positioning rather than code fixes. Implement what makes sense for your launch timeline.

## Recommendations to Implement

### 1: COMPETE-001 — Create competitive comparison content (Must, ~1 week)
**What:** Build a feature comparison matrix: this template vs Laravel Spark, SaaSyKit, Wave, Jetstream, and React starters (Shipfast, Makerkit).
**Key differentiators to highlight:**
- React + TypeScript + Inertia (vs Livewire/Vue competitors)
- 11 toggleable feature flags (vs monolithic starters)
- Redis-locked billing mutations (vs naive Cashier usage)
- 3 test frameworks: Pest + Vitest + Playwright + PHPStan + Infection
- Production-grade admin panel with impersonation
**Deliverables:** docs/competitive-comparison.md, optional landing page section.

### 2: BRAND-001 — Strengthen Welcome page value proposition (Must, ~3-5 days)
**What:** Current Welcome page messaging ("Start with the parts every SaaS needs") is generic. Upgrade to specific, compelling copy.
**Changes to resources/js/Pages/Welcome.tsx:**
- Add concrete numbers: "11 feature flags", "4 pricing tiers", "90+ tests"
- Add a "Who is this for?" section targeting personas (solo founder, small team, agency)
- Add a "Before vs After" section: "Without this: 2-3 months. With it: 2-3 days."
- Add social proof section (GitHub stars widget, testimonials)
- Fix documentation link (currently points to laravel.com/docs, should point to own docs)
**Deliverables:** Updated Welcome.tsx with stronger copy and social proof.

### 3: CAMPAIGN-001 — Build launch infrastructure (Must, ~2 weeks)
**What:** Currently no marketing site, demo, content pipeline, or community presence.
**Priority actions:**
1. Replace placeholder org URLs in README.md with real GitHub URLs
2. Add screenshots/GIFs to README (auth flow, admin panel, billing, onboarding)
3. Set up GitHub topics/tags for discoverability
4. Create a demo environment with seeded data
5. Prepare Product Hunt listing (tagline, gallery, maker video)
6. Plan content pieces: Laravel News article, Dev.to series, r/laravel post, Show HN
**Deliverables:** Updated README, demo deployment, PH listing draft.

### 4: EMAIL-001 — Implement email lifecycle sequences (Must, ~2 weeks)
**What:** Only 3 billing notifications exist. No welcome sequence, onboarding drip, or branded templates.
**Implementation order:**
1. Create branded email template in resources/views/vendor/mail/html/ (override Laravel defaults with logo, brand colors, footer)
2. Welcome email sequence (3 emails: immediate welcome, day 1 getting started, day 3 advanced features) — create WelcomeSequenceNotification + scheduled command
3. Trial nudge sequence (day 7 halfway, day 12 urgency, expired) — create TrialNudgeNotification
4. Re-engagement sequence (7/14/30 day inactivity) — requires last_active_at tracking
**Deliverables:** Branded email templates, 3 notification classes, 2 scheduled commands.

### 5: METRICS-001 — Define and surface product metrics (Should, ~2 weeks)
**What:** Admin panel has billing metrics but no product metrics framework.
**Implementation:**
1. Define north star metric and document in docs/METRICS.md
2. Replace placeholder Dashboard.tsx stats with real user-specific data
3. Add activation rate and signup-to-paid conversion to admin billing dashboard
4. Add customer health scoring based on login frequency, feature adoption, billing status
**Deliverables:** docs/METRICS.md, updated Dashboard.tsx, health scoring service.

## After All Fixes

```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "feat(gtm): competitive positioning, email sequences, branded templates, metrics framework"`
