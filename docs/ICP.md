# Ideal Customer Profile (ICP)

> Last updated: 2026-03-20. Review quarterly or when conversion data contradicts assumptions.

---

## Primary ICP: Laravel Developer Building First SaaS

### Firmographics

| Dimension | Profile |
|-----------|---------|
| **Role** | Software developer — Laravel as primary backend framework |
| **Company stage** | Pre-revenue to <$10k MRR; typically solo or 2-person team |
| **Team size** | 1–5 (usually 1–2 at purchase time) |
| **Geography** | Global; English-language developer communities |
| **Tech stack** | Laravel 10/11/12 + React or planning to adopt React |
| **Deployment** | VPS (Hetzner, DigitalOcean, Vultr) or cloud (AWS, Fly.io) |

### Pain Triggers (Why They Buy Now)

1. **Just left a job or accepted a contract gap** and is using the time to build the SaaS idea they've been putting off for 2 years
2. **Hit the "auth/billing treadmill"** — started building a new product and is on week 3 of setting up the same infrastructure they built for the last three projects
3. **Read a horror story about billing race conditions** (concurrent Stripe charge exploits) and wants the implementation solved before they launch
4. **Has a paying customer waiting** and needs to ship in days, not weeks
5. **Took on a client project** that needs a full SaaS stack delivered; can't spend the project budget rebuilding boilerplate

### Buying Process

- Solo decision — no procurement or legal review
- Buys within 24–72 hours of first landing on the page (high intent, low friction)
- Often finds the product via GitHub, Laravel community (Laracasts, r/laravel), or "laravel saas starter" search
- Primary objection: "Can I just build this myself?" → answer: yes, but it takes 2–3 months and you'll make mistakes in billing and security that this already solved
- Will not buy without reading code — GitHub or a live demo closes the deal

### Must-Have Criteria (Disqualify if Absent)

- Knows Laravel (has shipped at least one Laravel project)
- Building a product that will charge users (billing is a requirement, not a nice-to-have)
- Intends to deploy and maintain the server (not looking for a managed platform)
- Is comfortable with PHP 8.2+ and React 18

---

## Secondary Segment A: Developer at a Small Agency

### Profile

- Laravel developer at a 2–10 person agency
- Builds bespoke web products for clients
- Repeated pain: rebuilding auth, billing, admin for each client engagement
- **Architecture note**: This is a **single-tenant product**. Each client deployment is a separate installation. The "feature flags" value proposition is customizing scope per deployment, not per-client SaaS tenancy within one instance.
- Budget authority: often has discretionary budget for tooling (~$200–$500 one-time)

### Key Messaging Difference

"Start every client project from a tested, documented base. Feature flags let you turn subsystems on or off per deployment — ship only what each client needs."

Do **not** imply multi-tenant or white-label capabilities that would require a different architecture.

---

## Secondary Segment B: Technical Co-Founder Adding a Second Developer

### Profile

- Founder who built the initial product solo on Laravel
- Now hiring or bringing on a co-founder
- Wants TypeScript, Pest tests, and CI already configured before the new person starts
- Pain: "I know my codebase is a mess and I'm embarrassed to onboard someone"
- Target tier: **Pro Team** (2 seats, shared projects, audit log)

---

## Anti-Personas (Do Not Target)

These segments generate support overhead without proportional revenue. Messaging and onboarding should not attract them.

| Anti-Persona | Why They're Disqualified |
|--------------|--------------------------|
| **Livewire-only developers** | Prefer Blade/Livewire; the React admin panel is a cost, not a benefit |
| **Vue.js developers** | Wrong frontend; would need to replace the entire frontend layer |
| **Serverless/edge deployers** (Vercel, Cloudflare Workers) | Product requires long-running PHP processes, Redis, queues — incompatible with serverless |
| **Non-technical buyers** | No-code builders, product managers without dev background; can't customize or maintain the codebase |
| **Multi-tenant SaaS architects** | Need workspace/org scoping built in; single-tenant architecture is intentional here |
| **Node.js/Next.js-first developers** | Wrong backend stack entirely; point them to Shipfast or Supastarter comparisons |

---

## Conversion Metrics to Track

- `conversion_rate_landing_page_to_purchase_intent` — target: 2–4% (B2D benchmark)
- `trial_to_paid_conversion_rate_by_use_case` — track by onboarding `use_case` segment
- `support_ticket_volume_wrong_fit` — reduce by ≥10% after ICP-aligned messaging ships
- `agency_segment_refund_rate` — monitor after fixing agency persona messaging

---

## ICP Validation Checkpoints

Before changing any ICP assumptions, verify against:
1. `user_settings` table: `use_case` distribution from onboarding segmentation
2. Support tickets: tag by persona mismatch vs. product bugs
3. Churn interviews: what brought them here, what caused them to leave
4. UTM data: which channels produce the highest `trial_to_paid_conversion_rate`
