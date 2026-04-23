# docs/ Index

Quick navigation for the documentation directory.

## AI Workflow & Development Process

| File | Purpose |
|------|---------|
| [AI_WORKFLOW.md](AI_WORKFLOW.md) | **Start here.** Planning checklist (7 phases), implementation guardrails, TDD cycle, anti-pattern detectors, defense layers |
| [TESTING_GUIDELINES.md](TESTING_GUIDELINES.md) | Test quality rules, edge case checklist, what to assert |
| [DEBUGGING_GUIDE.md](DEBUGGING_GUIDE.md) | Test failure diagnosis protocol |
| [AI_PROMPT_TEMPLATES.md](AI_PROMPT_TEMPLATES.md) | Structured request templates for common tasks |

## Architecture & Design Decisions

| File | Purpose |
|------|---------|
| [ARCHITECTURE.md](ARCHITECTURE.md) | System design, sole-operator trade-offs, simplification decisions, scale headroom |
| [FEATURE_FLAGS.md](FEATURE_FLAGS.md) | Dependency graph, gating patterns, all 11 flags |
| [adr/](adr/) | Architecture Decision Records (see below) |

### ADRs

| ADR | Decision |
|-----|---------|
| [0001](adr/0001-feature-flag-architecture.md) | Feature flag architecture and route-dependent flag rules |
| [0002](adr/0002-redis-locked-billing-mutations.md) | Redis-locked billing mutations via BillingService |
| [0003](adr/0003-admin-cache-invalidation-strategy.md) | CacheInvalidationManager as single write path for admin cache |
| [0004](adr/0004-inertia-fire-and-forget-pattern.md) | Inertia router fire-and-forget: callbacks not awaits |
| [0005](adr/0005-admin-vs-super-admin-delegation.md) | Admin vs super-admin delegation model |
| [0006](adr/0006-lifecycle-simplification.md) | Keep lifecycle state machine, remove scoring/analytics platform |
| [0007](adr/0007-utm-analytics-removal.md) | Remove UTM capture and analytics gateway |
| [0008](adr/0008-scoring-stack-removal.md) | Remove engagement, lead, and customer-health scoring |

## Operations & Deployment

| File | Purpose |
|------|---------|
| [OPS.md](OPS.md) | VPS setup, nginx, supervisor, deployment checklist |
| [FORKING.md](FORKING.md) | How to fork the starter for a new product |
| [METRICS.md](METRICS.md) | Monitoring, alerting, observability |

## Content & SEO

| File | Purpose |
|------|---------|
| [ICP.md](ICP.md) | Ideal customer profile |
| [competitive-comparison.md](competitive-comparison.md) | Competitor analysis |

## Archive

[archive/](archive/) — superseded one-time artifacts (audits, phase-completion markers). Not actively maintained.
