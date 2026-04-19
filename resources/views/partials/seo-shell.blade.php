{{--
    SEO shell — crawl safety net for SPA pages when SSR is not rendering.

    Rendered only for guests (non-authenticated users) so public pages have
    an H1, lede, breadcrumbs, and internal links even when JavaScript is
    disabled or the SSR bundle is not running. After React hydrates, the
    React-rendered content in #app replaces what users see, but this div
    (outside #app) remains in the DOM. The `hidden` attribute and
    `aria-hidden="true"` prevent it from being visible or announced to
    users while still keeping it indexable by crawlers.
--}}
@php
    $seoTitle = $page['props']['title'] ?? null;
    $seoDescription = $page['props']['metaDescription'] ?? null;
    $seoBreadcrumbs = $page['props']['breadcrumbs'] ?? [];
    $appUrl = rtrim(config('app.url'), '/');
@endphp
<div id="seo-shell" hidden aria-hidden="true">
    @if ($seoTitle)
        <h1>{{ $seoTitle }}</h1>
    @endif
    @if ($seoDescription)
        <p>{{ $seoDescription }}</p>
    @endif
    @if (!empty($seoBreadcrumbs))
        <nav aria-label="Breadcrumb">
            @foreach ($seoBreadcrumbs as $crumb)
                @php $crumbUrl = Str::startsWith($crumb['url'] ?? '', ['https://', 'http://']) ? $crumb['url'] : '#'; @endphp
                <a href="{{ $crumbUrl }}">{{ $crumb['name'] ?? '' }}</a>
            @endforeach
        </nav>
    @endif
    <nav aria-label="Site navigation">
        <a href="{{ $appUrl }}/">Home</a>
        <a href="{{ $appUrl }}/features">Features</a>
        <a href="{{ $appUrl }}/pricing">Pricing</a>
        <a href="{{ $appUrl }}/compare">Compare</a>
        <a href="{{ $appUrl }}/guides">Guides</a>
        <a href="{{ $appUrl }}/blog">Blog</a>
        <a href="{{ $appUrl }}/changelog">Changelog</a>
        <a href="{{ $appUrl }}/about">About</a>
        <a href="{{ $appUrl }}/contact">Contact</a>
    </nav>
</div>
