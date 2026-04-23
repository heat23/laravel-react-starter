# Ops — Required CI Checks

These jobs must be green on every PR to main:

- php-tests (Pest, parallel, PCOV coverage)
- js-tests (Vitest)
- build (Vite production build)
- code-quality (Pint + PHPStan)
- e2e-tests (Playwright)

Configure GitHub branch protection accordingly.

All five jobs are defined in `.github/workflows/ci.yml`.
