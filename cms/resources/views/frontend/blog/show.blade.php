@extends('frontend.layout', [
    'page' => (object) [
        'title' => $post->title,
        'meta_title' => $post->meta_title ?: $post->title,
        'meta_description' => $post->meta_description ?: $post->excerpt,
        'meta_keywords' => '',
        'canonical_url' => route('frontend.blog.show', $post->slug),
        'og_title' => $post->meta_title ?: $post->title,
        'og_description' => $post->meta_description ?: $post->excerpt,
        'og_image' => $post->og_image ?: $post->featured_image,
        'twitter_title' => $post->meta_title ?: $post->title,
        'twitter_description' => $post->meta_description ?: $post->excerpt,
        'twitter_image' => $post->og_image ?: $post->featured_image,
        'show_header' => true,
        'show_footer' => true,
    ]
])

@section('content')
    <article class="pulse-post">
        <header class="pulse-post-header">
            <div class="pulse-site-container">
                <div class="pulse-blog-meta">
                    <span>{{ $post->category?->name ?? 'Uncategorized' }}</span>
                    <span>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</span>
                    <span>{{ $post->author?->name ?? 'Pulse Admin' }}</span>
                </div>

                <h1>{{ $post->title }}</h1>

                @if ($post->excerpt)
                    <p>{{ $post->excerpt }}</p>
                @endif
            </div>
        </header>

        @if ($post->featured_image)
            <section class="pulse-post-featured">
                <div class="pulse-site-container">
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}">
                </div>
            </section>
        @endif

        <section class="pulse-post-content-section">
            <div class="pulse-site-container pulse-post-content">
                {!! nl2br(e($post->content)) !!}

                @if ($post->tags->count())
                    <div class="pulse-post-tags">
                        @foreach ($post->tags as $tag)
                            <span>{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </article>
@endsection
