<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <link rel="canonical" href="{{ request()->url() }}" />

        <!-- Default Open Graph / Twitter meta tags (overridable per-page via Inertia Head) -->
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}" />
        <meta property="og:url" content="{{ request()->url() }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect x='2' y='2' width='28' height='28' rx='6' fill='%231e56e2'/%3E%3Cpath d='M9 16.5 14 21l9-10' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        {{-- Google Analytics 4 - Production Only --}}
        @production
            @if(config('services.google.analytics_id'))
                <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}" nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}"></script>
                <script nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '{{ config('services.google.analytics_id') }}');
                </script>
            @endif
        @endproduction

        <!-- JSON-LD Structured Data -->
        @php
            $jsonLd = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebApplication',
                'name' => config('app.name', 'Laravel'),
                'url' => config('app.url'),
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'All',
            ], JSON_UNESCAPED_SLASHES);
        @endphp
        <script type="application/ld+json" nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">{!! $jsonLd !!}</script>

        <!-- Scripts -->
        @routes(null, Illuminate\Support\Facades\Vite::cspNonce())
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
