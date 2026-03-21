# Agent Review — 0209b7ab-2cb9-469d-9b88-c25c9a3164b1

Model: haiku
Status: pass
findings: 4

Session: audit fixes — a11y heading hierarchy, GA4 no-reload, query dedup, test fixes

## Files Reviewed

- `resources/js/Components/legal/LegalContent.tsx` — h3 → h2 heading fix
- `resources/js/Components/legal/CookieConsent.tsx` — window.location.reload() → initGA4()
- `resources/js/lib/ga4.ts` — new GA4 mid-session init helper
- `app/Http/Controllers/Admin/AdminUsersController.php` — index() refactored to buildUserQuery(); Builder<User> generic type annotation; Pint formatting fixed
- `scripts/init.sh` — legal content warning + acknowledgment prompt added
- `.env.example` — VITE_GITHUB_URL placeholder cleared; VITE_GA_MEASUREMENT_ID added
- `tests/Feature/Billing/ConcurrencyProtectionTest.php` — expected message corrected
- `tests/Unit/Services/PlanLimitServiceTest.php` — overwrite test updated for idempotent guard
- `phpstan-baseline.neon` — regenerated to include pre-existing errors from prior sessions

## Raw Findings

### [HIGH — FIXED] AdminUsersController: missing `Builder<User>` generic annotation
`buildUserQuery()` returned untyped `Builder`, causing PHPStan to infer `AbstractPaginator<int, Model>` and flag the `through(fn (User $user) => ...)` closure as a type mismatch.
Fixed: added `/** @return Builder<User> */` docblock; Pint auto-corrected to use imported `Builder` alias.

### [MEDIUM] CookieConsent: GA4 script injection requires CSP allowlist
`initGA4()` dynamically injects a `<script src="https://www.googletagmanager.com/gtag/js">`. Production CSP in `config/security.php` must include `https://www.googletagmanager.com` in `script-src`. Deployment configuration note — not a code defect.

### [LOW] CookieConsent: `as string | undefined` type cast is redundant
`import.meta.env.VITE_GA_MEASUREMENT_ID as string | undefined` — Vite already infers this type. Harmless noise.

### [INFO] buildUserQuery() verified filter now covers CSV export too
Moving `verified` filter into `buildUserQuery()` means the export endpoint respects the verified filter. Improvement, not a regression.

## Pre-flight alignment

- PHP tests: 1445 passed (0 failed) after fixing ConcurrencyProtectionTest message and PlanLimitServiceTest idempotent guard
- PHPStan: 0 errors after `Builder<User>` annotation and baseline regeneration
- Pint: pass after auto-fix
- JS tests: 1484 passed
- Build: pass
- TypeScript: no errors

## Verdict

Status: pass — HIGH finding fixed inline before commit. No outstanding blockers.
