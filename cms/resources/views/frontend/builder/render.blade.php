@foreach ($blocks as $block)
    @switch($block['type'] ?? '')
        @case('hero')
            <section class="pulse-builder-hero">
                <div class="pulse-site-container">
                    <span class="pulse-eyebrow">{{ $block['eyebrow'] ?? 'Pulse CMS' }}</span>
                    <h1>{{ $block['title'] ?? '' }}</h1>
                    <p>{{ $block['description'] ?? '' }}</p>

                    @if (! empty($block['button_label']))
                        <a href="{{ $block['button_url'] ?? '#' }}" class="pulse-site-btn">
                            {{ $block['button_label'] }}
                        </a>
                    @endif
                </div>
            </section>
        @break

        @case('text')
            <section class="pulse-builder-section">
                <div class="pulse-site-container pulse-content-card">
                    {!! nl2br(e($block['content'] ?? '')) !!}
                </div>
            </section>
        @break

        @case('image')
            <section class="pulse-builder-section">
                <div class="pulse-site-container">
                    <img class="pulse-builder-image" src="{{ $block['url'] ?? '' }}" alt="{{ $block['alt'] ?? '' }}">
                </div>
            </section>
        @break

        @case('video')
            <section class="pulse-builder-section">
                <div class="pulse-site-container">
                    <div class="pulse-builder-video">
                        {!! $block['embed'] ?? '' !!}
                    </div>
                </div>
            </section>
        @break

        @case('cta')
            <section class="pulse-builder-cta">
                <div class="pulse-site-container">
                    <h2>{{ $block['title'] ?? '' }}</h2>
                    <p>{{ $block['description'] ?? '' }}</p>

                    @if (! empty($block['button_label']))
                        <a href="{{ $block['button_url'] ?? '#' }}" class="pulse-site-btn">
                            {{ $block['button_label'] }}
                        </a>
                    @endif
                </div>
            </section>
        @break

        @case('features')
            <section class="pulse-builder-section">
                <div class="pulse-site-container">
                    <div class="pulse-section-head">
                        <h2>{{ $block['title'] ?? 'Features' }}</h2>
                        <p>{{ $block['description'] ?? '' }}</p>
                    </div>

                    <div class="pulse-builder-grid">
                        @foreach (($block['items'] ?? []) as $item)
                            <article class="pulse-builder-card">
                                <span class="material-symbols-rounded">{{ $item['icon'] ?? 'check_circle' }}</span>
                                <h3>{{ $item['title'] ?? '' }}</h3>
                                <p>{{ $item['description'] ?? '' }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @break

        @case('stats')
            <section class="pulse-builder-section">
                <div class="pulse-site-container pulse-builder-stats">
                    @foreach (($block['items'] ?? []) as $item)
                        <article>
                            <strong>{{ $item['value'] ?? '' }}</strong>
                            <span>{{ $item['label'] ?? '' }}</span>
                        </article>
                    @endforeach
                </div>
            </section>
        @break

        @case('accordion')
            <section class="pulse-builder-section">
                <div class="pulse-site-container">
                    <div class="pulse-section-head">
                        <h2>{{ $block['title'] ?? 'Frequently Asked Questions' }}</h2>
                    </div>

                    <div class="pulse-builder-accordion">
                        @foreach (($block['items'] ?? []) as $item)
                            <details>
                                <summary>{{ $item['question'] ?? '' }}</summary>
                                <p>{{ $item['answer'] ?? '' }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </section>
        @break

        @case('testimonial')
            <section class="pulse-builder-section">
                <div class="pulse-site-container pulse-builder-testimonial">
                    <p>“{{ $block['quote'] ?? '' }}”</p>
                    <strong>{{ $block['name'] ?? '' }}</strong>
                    <span>{{ $block['role'] ?? '' }}</span>
                </div>
            </section>
        @break

        @case('html')
            <section class="pulse-builder-section">
                <div class="pulse-site-container">
                    {!! $block['html'] ?? '' !!}
                </div>
            </section>
        @break
    @endswitch
@endforeach
