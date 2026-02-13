<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
@if(config('features.billing.enabled'))
    <url>
        <loc>{{ url('/pricing') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
@endif
    <url>
        <loc>{{ url('/login') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>{{ url('/register') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
</urlset>
