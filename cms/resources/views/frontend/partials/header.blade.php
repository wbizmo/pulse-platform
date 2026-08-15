<header class="pulse-site-header">
    <div class="pulse-site-container pulse-site-header-inner">
        <a href="{{ route('frontend.home') }}" class="pulse-site-brand">
            @if (! empty($themeSettings['logo_url']))
                <img src="{{ $themeSettings['logo_url'] }}" alt="{{ $settings['site_name'] ?? 'Pulse CMS' }}" class="pulse-site-logo-img">
            @else
                <span class="pulse-site-logo">
                    {{ strtoupper(substr($settings['site_name'] ?? 'Pulse', 0, 1)) }}
                </span>
            @endif

            <span>
                <strong>{{ $settings['site_name'] ?? 'Pulse CMS' }}</strong>
                <small>{{ $settings['site_tagline'] ?? 'Laravel-powered CMS' }}</small>
            </span>
        </a>

        <nav class="pulse-site-nav">
            @if ($mainMenu)
                @foreach ($mainMenu->items as $item)
                    <a href="{{ $item->href() }}" target="{{ $item->target }}" @if($item->rel()) rel="{{ $item->rel() }}" @endif>
                        {{ $item->label }}
                    </a>
                @endforeach
            @else
                <a href="{{ route('frontend.home') }}">Home</a>
                <a href="/about">About</a>
                <a href="/contact">Contact</a>
            @endif
        </nav>
    </div>
</header>
