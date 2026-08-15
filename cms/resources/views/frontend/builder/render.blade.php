@foreach ($nodes as $node)
    @php
        $props = $node['props'];
        $settings = $node['settings'];
        $classes = ['pulse-builder-node', 'pulse-builder-'.$node['type']];
        if (isset($settings['alignment'])) $classes[] = 'pulse-align-'.$settings['alignment'];
        if (isset($settings['spacing'])) $classes[] = 'pulse-space-'.$settings['spacing'];
        if (isset($settings['width'])) $classes[] = 'pulse-width-'.$settings['width'];
        foreach (($settings['hide_on'] ?? []) as $breakpoint) $classes[] = 'pulse-hide-'.$breakpoint;
    @endphp
    <section id="builder-{{ $node['id'] }}" class="{{ implode(' ', $classes) }}">
        <div class="pulse-site-container">
            @switch($node['type'])
                @case('section') @case('columns')
                    <div class="pulse-builder-children pulse-builder-layout-{{ $props['layout'] ?? $props['variant'] ?? 'plain' }}">
                        @include('frontend.builder.render', ['nodes' => $node['children'], 'builderMedia' => $builderMedia])
                    </div>
                @break
                @case('hero')
                    @if ($props['eyebrow'] !== '')<span class="pulse-eyebrow">{{ $props['eyebrow'] }}</span>@endif
                    <h1>{{ $props['title'] }}</h1><p>{{ $props['description'] }}</p>
                    @if ($props['button_label'] !== '' && $props['button_url'] !== '')<a class="pulse-site-btn" href="{{ $props['button_url'] }}">{{ $props['button_label'] }}</a>@endif
                @break
                @case('text') <div class="pulse-content-card">{!! nl2br(e($props['content'])) !!}</div> @break
                @case('image')
                    @php($image = $props['media_id'] ? $builderMedia->get($props['media_id']) : null)
                    @if ($image)<img class="pulse-builder-image" src="{{ $image->public_url }}" alt="{{ $props['alt'] ?: $image->alt_text }}">@endif
                @break
                @case('video')
                    @if ($props['url'] !== '')<p><a href="{{ $props['url'] }}" rel="noopener noreferrer">Watch video</a></p>@endif
                @break
                @case('cta')
                    <h2>{{ $props['title'] }}</h2><p>{{ $props['description'] }}</p>
                    @if ($props['button_label'] !== '' && $props['button_url'] !== '')<a class="pulse-site-btn" href="{{ $props['button_url'] }}">{{ $props['button_label'] }}</a>@endif
                @break
                @case('features')
                    <h2>{{ $props['title'] }}</h2><p>{{ $props['description'] }}</p><div class="pulse-builder-grid">@foreach($props['items'] as $item)<article><h3>{{ $item['title'] }}</h3><p>{{ $item['description'] }}</p></article>@endforeach</div>
                @break
                @case('stats') <div class="pulse-builder-stats">@foreach($props['items'] as $item)<article><strong>{{ $item['value'] }}</strong><span>{{ $item['label'] }}</span></article>@endforeach</div> @break
                @case('accordion') <h2>{{ $props['title'] }}</h2>@foreach($props['items'] as $item)<details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>@endforeach @break
                @case('testimonial') <blockquote><p>“{{ $props['quote'] }}”</p><footer>{{ $props['name'] }}@if($props['role']) — {{ $props['role'] }}@endif</footer></blockquote> @break
            @endswitch
        </div>
    </section>
@endforeach
