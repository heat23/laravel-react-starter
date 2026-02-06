<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <link rel="canonical" href="{{ request()->url() }}" />

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
