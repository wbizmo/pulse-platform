<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $fontFamily = $themeSettings['font_family'] ?? 'Inter';
        $primaryColor = $themeSettings['primary_color'] ?? '#111827';
        $secondaryColor = $themeSettings['secondary_color'] ?? '#2563eb';
        $buttonRadius = $themeSettings['button_radius'] ?? '16px';
        $customCss = trim($themeSettings['custom_css'] ?? '');
        $showBackToTop = ($themeSettings['show_back_to_top'] ?? '1') === '1';
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo->title }}</title>
    @if ($seo->description !== '')<meta name="description" content="{{ $seo->description }}">@endif
    @if ($seo->keywords)<meta name="keywords" content="{{ $seo->keywords }}">@endif
    <meta name="robots" content="{{ $seo->robots }}">
    @if ($seo->canonical)<link rel="canonical" href="{{ $seo->canonical }}">@endif
    @if ($seo->socialEnabled)
        <meta property="og:title" content="{{ $seo->ogTitle }}">
        <meta property="og:description" content="{{ $seo->ogDescription }}">
        <meta property="og:type" content="{{ $seo->ogType }}">
        <meta property="og:url" content="{{ $seo->canonical }}">
        @if ($seo->ogImage)<meta property="og:image" content="{{ $seo->ogImage }}">@endif
        <meta name="twitter:card" content="{{ $seo->twitterImage ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $seo->twitterTitle }}">
        <meta name="twitter:description" content="{{ $seo->twitterDescription }}">
        @if ($seo->twitterImage)<meta name="twitter:image" content="{{ $seo->twitterImage }}">@endif
    @endif
    @if ($seo->structuredData)
        <script type="application/ld+json">{!! json_encode($seo->structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif

    @if (! empty($themeSettings['favicon_url']))
        <link rel="icon" href="{{ $themeSettings['favicon_url'] }}">
    @endif

    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontFamily) }}:wght@300;400;500;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,300,0,0&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">

    <style>
        :root {
            --site-primary: {{ $primaryColor }};
            --site-secondary: {{ $secondaryColor }};
            --site-font: "{{ $fontFamily }}", sans-serif;
            --site-button-radius: {{ $buttonRadius }};
        }
    </style>

    @if ($customCss !== '')
        <style id="pulse-custom-theme-css">
            {!! $customCss !!}
        </style>
    @endif

</head>
<body class="
    pulse-theme-{{ $theme?->slug ?? 'default' }}
    pulse-header-{{ $themeSettings['header_style'] ?? 'classic' }}
    pulse-footer-{{ $themeSettings['footer_style'] ?? 'classic' }}
">
    <div class="pulse-site">
        @if ((isset($page) ? $page->show_header : true))
            @include('frontend.partials.header')
        @endif

        <main>
            @yield('content')
        </main>

        @if ((isset($page) ? $page->show_footer : true))
            @include('frontend.partials.footer')
        @endif
    </div>

    @if ($showBackToTop)
        <button type="button" class="pulse-back-to-top" data-back-to-top aria-label="Back to top">
            <span class="material-symbols-rounded">keyboard_arrow_up</span>
        </button>
    @endif

    <script src="{{ asset('js/frontend.js') }}" defer></script>
</body>
</html>
