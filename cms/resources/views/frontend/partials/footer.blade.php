<footer class="pulse-site-footer">
    <div class="pulse-site-container pulse-site-footer-grid">
        <div>
            <h3>{{ $settings['site_name'] ?? 'Pulse CMS' }}</h3>
            <p>{{ $settings['site_tagline'] ?? 'A flexible Laravel-powered CMS for modern websites.' }}</p>
        </div>

        <div>
            <h4>Navigation</h4>

            <div class="pulse-footer-links">
                @if ($footerMenu)
                    @foreach ($footerMenu->items as $item)
                        <a href="{{ $item->href() }}" target="{{ $item->target }}" @if($item->rel()) rel="{{ $item->rel() }}" @endif>
                            {{ $item->label }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('frontend.home') }}">Home</a>
                    <a href="/privacy-policy">Privacy Policy</a>
                    <a href="/terms">Terms</a>
                @endif
            </div>
        </div>

        <div>
            <h4>Contact</h4>

            <div class="pulse-footer-links">
                @if (($settings['show_email'] ?? '0') == '1')
                    <span>{{ $settings['contact_email'] ?? 'hello@example.com' }}</span>
                @endif

                @if (($settings['show_phone'] ?? '0') == '1')
                    <span>{{ $settings['contact_phone'] ?? '' }}</span>
                @endif

                @if (($settings['show_address'] ?? '0') == '1')
                    <span>{{ $settings['contact_address'] ?? '' }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="pulse-site-container pulse-site-footer-bottom">
        <span>© {{ date('Y') }} {{ $settings['site_name'] ?? 'Pulse CMS' }}. All rights reserved.</span>
    </div>
</footer>
