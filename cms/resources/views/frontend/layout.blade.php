<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $seoTitle = $page->meta_title ?: ($settings['seo_default_title'] ?? $page->title);
        $seoDescription = $page->meta_description ?: ($settings['seo_default_description'] ?? ($settings['site_tagline'] ?? ''));
        $seoKeywords = $page->meta_keywords ?? ($settings['seo_default_keywords'] ?? '');
        $seoOgTitle = $page->og_title ?: $seoTitle;
        $seoOgDescription = $page->og_description ?: $seoDescription;
        $seoOgImage = $page->og_image ?: ($settings['seo_default_og_image'] ?? null);
        $seoTwitterTitle = $page->twitter_title ?: $seoTitle;
        $seoTwitterDescription = $page->twitter_description ?: $seoDescription;
        $seoTwitterImage = $page->twitter_image ?: $seoOgImage;
        $canonicalUrl = $page->canonical_url ?: url()->current();

        $fontFamily = $themeSettings['font_family'] ?? 'Inter';
        $primaryColor = $themeSettings['primary_color'] ?? '#111827';
        $secondaryColor = $themeSettings['secondary_color'] ?? '#2563eb';
        $buttonRadius = $themeSettings['button_radius'] ?? '16px';
        $customCss = trim($themeSettings['custom_css'] ?? '');

        $schemaEnabled = ($settings['seo_schema_enabled'] ?? '1') === '1';
        $canonicalEnabled = ($settings['seo_canonical_enabled'] ?? '1') === '1';
        $socialEnabled = ($settings['seo_social_enabled'] ?? '1') === '1';
        $noindexEnabled = ($settings['seo_noindex_enabled'] ?? '0') === '1';
        $showBackToTop = ($themeSettings['show_back_to_top'] ?? '1') === '1';

        $schemaType = $settings['seo_schema_type'] ?? 'WebSite';
        $organizationName = $settings['seo_organization_name'] ?? ($settings['site_name'] ?? 'Pulse CMS');
        $organizationLogo = $settings['seo_organization_logo'] ?? ($themeSettings['logo_url'] ?? null);
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle }}</title>

    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">

    @if ($noindexEnabled)
        <meta name="robots" content="noindex,nofollow">
    @endif

    @if ($canonicalEnabled)
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif

    @if ($socialEnabled)
        <meta property="og:title" content="{{ $seoOgTitle }}">
        <meta property="og:description" content="{{ $seoOgDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">

        @if ($seoOgImage)
            <meta property="og:image" content="{{ $seoOgImage }}">
        @endif

        <meta name="twitter:card" content="{{ $seoTwitterImage ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $seoTwitterTitle }}">
        <meta name="twitter:description" content="{{ $seoTwitterDescription }}">

        @if ($seoTwitterImage)
            <meta name="twitter:image" content="{{ $seoTwitterImage }}">
        @endif
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

    @if ($schemaEnabled)
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => $schemaType,
                'name' => $organizationName,
                'url' => url('/'),
                'logo' => $organizationLogo,
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endif
</head>
<body class="
    pulse-theme-{{ $theme?->slug ?? 'default' }}
    pulse-header-{{ $themeSettings['header_style'] ?? 'classic' }}
    pulse-footer-{{ $themeSettings['footer_style'] ?? 'classic' }}
">
    <div class="pulse-site">
        @if ($page->show_header)
            @include('frontend.partials.header')
        @endif

        <main>
            @yield('content')
        </main>

        @if ($page->show_footer)
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
