<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->title }}</title>

    <meta name="description" content="{{ $page->meta_description ?: ($settings['site_tagline'] ?? '') }}">
    <meta name="keywords" content="{{ $page->meta_keywords ?? '' }}">

    @if ($page->canonical_url)
        <link rel="canonical" href="{{ $page->canonical_url }}">
    @endif

    <meta property="og:title" content="{{ $page->og_title ?: $page->title }}">
    <meta property="og:description" content="{{ $page->og_description ?: ($page->meta_description ?? '') }}">

    @if ($page->og_image)
        <meta property="og:image" content="{{ $page->og_image }}">
    @endif

    <meta name="twitter:title" content="{{ $page->twitter_title ?: $page->title }}">
    <meta name="twitter:description" content="{{ $page->twitter_description ?: ($page->meta_description ?? '') }}">

    @if ($page->twitter_image)
        <meta name="twitter:image" content="{{ $page->twitter_image }}">
    @endif

    @if (! empty($themeSettings['favicon_url']))
        <link rel="icon" href="{{ $themeSettings['favicon_url'] }}">
    @endif

    @php
        $fontFamily = $themeSettings['font_family'] ?? 'Inter';
    @endphp

    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontFamily) }}:wght@300;400;500;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,300,0,0&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">

    <style>
        :root {
            --site-primary: {{ $themeSettings['primary_color'] ?? '#111827' }};
            --site-secondary: {{ $themeSettings['secondary_color'] ?? '#2563eb' }};
            --site-font: "{{ $fontFamily }}", sans-serif;
            --site-button-radius: {{ $themeSettings['button_radius'] ?? '16px' }};
        }

        {!! $themeSettings['custom_css'] ?? '' !!}
    </style>
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
</body>
</html>
