# SEO Conventions (Public Pages)

This project has substantial public marketing surface (/, /pricing, /features/*, /compare/*, /guides/*, /blog/*, /about, /contact, /changelog, /roadmap). These rules prevent the class of SEO bugs uncovered in an Ahrefs crawl of a production site built on this starter.

## JSON-LD Rules (CRITICAL)

### Offer.price must be numeric, never a string

```php
// WRONG — fails Google Rich Results validation
'price' => '0'
'price' => (string) $price

// CORRECT
'price' => 0
'price' => (int) $price
```

### Every top-level entity needs @id and cross-references

All JSON-LD entities in `app.blade.php` (`SoftwareApplication`, `Organization`, `WebSite`, `WebPage`) must have `@id` with a URL fragment rooted at `config('app.url')`:

| Type | @id fragment |
|------|-------------|
| Organization | `#organization` |
| WebSite | `#website` |
| SoftwareApplication | `#software` |
| WebPage | `{canonicalUrl}#webpage` |

Cross-references that must be wired:
- `SoftwareApplication.publisher → { '@id': baseUrl.'#organization' }`
- `WebSite.publisher → { '@id': baseUrl.'#organization' }`
- `WebPage.isPartOf → { '@id': baseUrl.'#website' }`
- `WebPage.publisher → { '@id': baseUrl.'#organization' }`

### Organization.logo must be an ImageObject

```php
// WRONG
'logo' => 'https://example.com/logo.png'

// CORRECT
'logo' => [
    '@type' => 'ImageObject',
    'url' => $baseUrl.'/images/og-default.png',
    'width' => 1200,
    'height' => 630,
]
```

### Article image must be ImageObject with dimensions

When emitting `Article` or `BlogPosting` JSON-LD:

```php
// WRONG
'image' => 'https://example.com/image.png'

// CORRECT
'image' => [
    '@type' => 'ImageObject',
    'url' => $imageUrl,
    'width' => 1200,
    'height' => 630,
]
'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl]
```

### Title length ≤60 characters

All hardcoded `<title>` strings (in controllers, SEO builder methods, React `<Head>`) must be ≤60 characters. Do NOT rely on the browser to truncate — Google truncates at display time and counts bytes.

Check: run `tests/Feature/Seo/TitleLengthTest.php`.

## SEO Shell (Blade Crawl Safety Net)

`resources/views/partials/seo-shell.blade.php` is included in `app.blade.php` for guests. It injects a `hidden` div with H1, lede, breadcrumbs, and internal nav before the React app div. This ensures crawlers see content even when SSR is off.

**When adding a new public route:**
1. The route is already covered by the shell's static nav links if it's a core nav page.
2. If it's a new content type, ensure the controller passes `title`, `metaDescription`, `canonicalUrl`, and `breadcrumbs` as Inertia props — the shell reads these from `$page['props']`.
3. Add the new route to `tests/Feature/Seo/SeoShellRendersContentTest.php`.
4. Add the new route to `tests/Feature/Seo/TitleLengthTest.php`.

## SSR

SSR build is wired in `package.json`: `"build": "vite build && vite build --ssr"`. Both bundles must compile together. Configure `INERTIA_SSR_ENABLED=true` and start `bootstrap/ssr/ssr.mjs` in production for full server-rendered responses. The SEO shell is a fallback only.

## Tests

Run the three SEO test files before claiming a public-route feature done:

```bash
php artisan test --filter=Seo --compact
```

- `tests/Feature/Seo/JsonLdValidityTest.php` — numeric prices, @id linking, ImageObject shapes
- `tests/Feature/Seo/SeoShellRendersContentTest.php` — H1 + ≥3 internal links per public route
- `tests/Feature/Seo/TitleLengthTest.php` — title ≤60 chars per public route

## Sitemap

`SeoController::buildSitemap()` maintains a static URL list. After adding a new public route, add it to `buildSitemap()` with appropriate `priority` and `changefreq`.
