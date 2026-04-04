---
description: Migration safety rules — nullable columns, foreign keys, two-phase deploys
globs:
  - database/migrations/*
---

# Migration Conventions

Follow global CLAUDE.md > Database Safety rules (nullable columns, constrained FKs, two-phase deploys, `Schema::hasColumn()` checks).

Additionally:
- Feature-conditional migrations: only for whole-table creation (`Schema::hasTable` check). Never gate column additions/removals on feature flags — causes schema drift.
- New columns on existing tables: always nullable or with default (never bare NOT NULL)
- Foreign keys: `->constrained()->cascadeOnDelete()` + index
- Hard deletes on all models by default (no `SoftDeletes`). Project-level overrides for specific models only.
- Two-phase deploys for destructive schema changes: deploy code that stops using column first, drop column in next deploy
