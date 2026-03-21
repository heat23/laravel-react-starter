<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <link rel="canonical" href="{{ request()->url() }}" />

        <!-- Robot / AI crawler meta tags -->
        @auth
        <meta name="robots" content="noindex, nofollow" />
        <meta name="google-extended" content="noindex" />
        @else
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
        <meta name="google-extended" content="index" />
        @endauth

        <!-- Default Open Graph / Twitter meta tags (overridable per-page via Inertia Head) -->
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}" />
        <meta property="og:url" content="{{ request()->url() }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta property="og:image" content="{{ asset('images/og-default.png') }}" />
        <meta name="twitter:image" content="{{ asset('images/og-default.png') }}" />
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect x='2' y='2' width='28' height='28' rx='6' fill='%231e56e2'/%3E%3Cpath d='M9 16.5 14 21l9-10' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        {{-- Google Analytics 4 - Production Only (gated on cookie consent) --}}
        @production
            @if(config('services.google.analytics_id'))
                <script nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">
                    (function() {
                        try {
                            var cats = {};
                            try { cats = JSON.parse(localStorage.getItem('cookie_consent_categories') || '{}'); } catch(e) {}
                            // Backward compat: honour old binary consent if new categories not yet stored
                            var analyticsOk = cats.analytics === true ||
                                (!localStorage.getItem('cookie_consent_categories') && localStorage.getItem('cookie_consent') === 'accepted');
                            if (analyticsOk) {
                                var id = '{{ config('services.google.analytics_id') }}';
                                var s = document.createElement('script');
                                s.async = true;
                                s.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
                                document.head.appendChild(s);
                                window.dataLayer = window.dataLayer || [];
                                function gtag(){dataLayer.push(arguments);}
                                gtag('js', new Date());
                                gtag('config', id);
                            }
                        } catch(e) {}
                    })();
                </script>
            @endif
        @endproduction

        <!-- JSON-LD Structured Data: SoftwareApplication -->
        @php
            $offersBlock = ['@type' => 'Offer', 'priceCurrency' => 'USD', 'price' => '0', 'description' => 'Contact for pricing', 'availability' => 'https://schema.org/InStock'];
            if (config('features.billing.enabled', false)) {
                $plans = config('plans', []);
                $startingPrice = collect($plans)->filter(fn ($p) => ($p['price_monthly'] ?? 0) > 0)->min('price_monthly');
                if ($startingPrice) {
                    $offersBlock['price'] = (string) $startingPrice;
                    $offersBlock['description'] = 'Starting price per month';
                }
            }
            $softwareAppLd = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => config('app.name', 'Laravel'),
                'url' => config('app.url'),
                'applicationCategory' => 'DeveloperApplication',
                'operatingSystem' => 'Web',
                'description' => 'Production-ready Laravel 12 + React 18 + TypeScript SaaS starter kit with Stripe billing, admin panel, feature flags, and 90+ tests.',
                'offers' => $offersBlock,
                'featureList' => [
                    'Stripe billing with Redis-locked mutations',
                    '11 feature flags with database overrides',
                    'React + TypeScript admin panel',
                    'TOTP two-factor authentication',
                    'Social auth (Google + GitHub OAuth)',
                    'Outgoing and incoming webhooks',
                    'Audit logging',
                    '90+ automated tests',
                ],
                'softwareVersion' => '1.0',
                'screenshot' => rtrim(config('app.url'), '/').'/images/og-default.png',
            ], JSON_UNESCAPED_SLASHES);
        @endphp
        <script type="application/ld+json" nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">{!! $softwareAppLd !!}</script>

        <!-- JSON-LD Structured Data: Organization -->
        @php
            $organizationLd = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => config('app.name', 'Laravel'),
                'url' => config('app.url'),
                'logo' => rtrim(config('app.url'), '/').'/images/og-default.png',
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'contactType' => 'customer support',
                    'url' => rtrim(config('app.url'), '/').'/contact',
                ],
            ], JSON_UNESCAPED_SLASHES);
        @endphp
        <script type="application/ld+json" nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">{!! $organizationLd !!}</script>

        <!-- JSON-LD Structured Data: FAQPage (homepage only) -->
        @if(isset($page['component']) && $page['component'] === 'Welcome')
        @php
            $faqItems = $page['props']['faqs'] ?? [];
            if (!empty($faqItems)) {
                $faqLd = json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(fn ($faq) => [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq['answer'],
                        ],
                    ], $faqItems),
                ], JSON_UNESCAPED_SLASHES);
            }
        @endphp
        @if(isset($faqLd))
        <script type="application/ld+json" nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">{!! $faqLd !!}</script>
        @endif
        @endif

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
