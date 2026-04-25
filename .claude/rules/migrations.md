---
description: Migration safety rules — nullable columns, foreign keys, two-phase deploys
globs:
  - database/migrations/*
---

# Migration Conventions

Global CLAUDE.md > Database Safety covers nullable defaults, FKs, two-phase deploys, `Schema::hasColumn()` checks. Project-specific addition:

- **Feature-conditional migrations:** Only gate WHOLE-TABLE creation behind feature flags (use `Schema::hasTable` check). Never gate column add/drop on flags — causes schema drift between environments.
- **Migration column modify** must include ALL previously defined attributes (Laravel drops anything omitted).
- **`SoftDeletes` is opt-in per model.** Default = hard delete. Explicit project overrides only (e.g., `Site` model).
