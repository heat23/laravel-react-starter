---
description: SEO conventions for public marketing pages — JSON-LD shapes, title length, Blade SEO shell
globs:
  - app/Http/Controllers/SeoController.php
  - app/Http/Controllers/MarketingController.php
  - app/Http/Controllers/BlogController.php
  - resources/views/app.blade.php
  - resources/views/partials/seo-shell.blade.php
  - resources/js/Pages/Marketing/**
  - resources/js/Pages/Blog/**
  - resources/js/Pages/Compare/**
  - resources/js/Pages/Features/**
  - resources/js/Pages/Guides/**
---

# SEO Conventions (Public Pages)

Public surface: `/`, `/pricing`, `/features/*`, `/compare/*`, `/guides/*`, `/blog/*`, `/about`, `/contact`, `/changelog`, `/roadmap`. Rules below prevent Google Rich Results validation failures and empty-DOM crawl issues.

## JSON-LD Required Shapes

All JSON-LD lives in `resources/views/app.blade.php`. Every shape below is enforced by `tests/Feature/Seo/JsonLdValidityTest.php`.

**Numeric prices.** `Offer.price` must be `int|float`, never string. `(int) $price` not `(string) $price`.

**`@id` + cross-references.** Top-level entities need a stable `@id` rooted at `config('app.url')`:

| Entity | `@id` fragment |
|--------|----------------|
| Organization | `#organization` |
| WebSite | `#website` |
| SoftwareApplication | `#software` |
| WebPage | `{canonicalUrl}#webpage` |

Required wires:
- `SoftwareApplication.publisher.@id` → `#organization`
- `WebSite.publisher.@id` → `#organization`
- `WebPage.isPartOf.@id` → `#website`
- `WebPage.publisher.@id` → `#organization`

**Logos and images = `ImageObject` with `width` + `height`.** Never bare URL strings. Default OG image: `/images/og-default.png` (1200×630).

```php
'logo' => ['@type' => 'ImageObject', 'url' => $url, 'width' => 1200, 'height' => 630]
```

For `Article`/`BlogPosting`: include `mainEntityOfPage` referencing `{canonicalUrl}`.

## Title Length ≤60 chars

All hardcoded `<title>` strings (controllers, SEO builder methods, React `<Head>`) ≤60 chars. Google truncates at display by byte count — do not rely on browser truncation. Enforced by `tests/Feature/Seo/TitleLengthTest.php`.

## Blade SEO Shell (Crawler Fallback)

`resources/views/partials/seo-shell.blade.php` is included in `app.blade.php` for guests. Renders a `hidden` div with H1, lede, breadcrumbs, internal nav before the React app div — guarantees crawler content even with SSR off.

**Adding a new public route:**
1. If covered by core nav, no extra work.
2. New content type: controller MUST pass `title`, `metaDescription`, `canonicalUrl`, `breadcrumbs` as Inertia props (shell reads from `$page['props']`).
3. Add the route to `tests/Feature/Seo/SeoShellRendersContentTest.php` AND `tests/Feature/Seo/TitleLengthTest.php`.
4. Add to `SeoController::buildSitemap()` with `priority` + `changefreq`.

## SSR

Not used. Blade SEO shell is the canonical fallback. `package.json` builds a single client bundle. To re-enable: add `ssr: 'resources/js/ssr.tsx'` to `laravel()` plugin in `vite.config.ts` + a second `vite build --ssr` step in build script.

## Verification

```bash
php artisan test --filter=Seo --compact
```

- `JsonLdValidityTest` — numeric prices, `@id` linking, ImageObject shapes
- `SeoShellRendersContentTest` — H1 + ≥3 internal links per public route
- `TitleLengthTest` — ≤60 chars per public route
