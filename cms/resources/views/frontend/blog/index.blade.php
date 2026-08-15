@extends('frontend.layout')

@section('content')
    <section class="pulse-blog-hero">
        <div class="pulse-site-container">
            <span class="pulse-eyebrow">{{ $archiveType ?? 'Pulse Blog' }}</span>
            <h1>{{ $archiveTitle ?? 'Latest Posts' }}</h1>
            <p>{{ $archiveDescription ?? 'Read the latest posts, updates, insights, and announcements published from Pulse CMS.' }}</p>
        </div>
    </section>

    <section class="pulse-blog-section">
        <div class="pulse-site-container">
            <div class="pulse-blog-grid">
                @forelse ($posts as $post)
                    <article class="pulse-blog-card">
                        @if ($post->featuredMedia?->public_url)
                            <a href="{{ route('frontend.blog.show', $post->slug) }}" class="pulse-blog-image">
                                <img src="{{ $post->featuredMedia?->public_url }}" alt="{{ $post->title }}">
                            </a>
                        @else
                            <a href="{{ route('frontend.blog.show', $post->slug) }}" class="pulse-blog-image pulse-blog-image-empty">
                                <span class="material-symbols-rounded">article</span>
                            </a>
                        @endif

                        <div class="pulse-blog-card-body">
                            <div class="pulse-blog-meta">
                                <span>{{ $post->category?->name ?? 'Uncategorized' }}</span>
                                <span>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</span>
                            </div>

                            <h2>
                                <a href="{{ route('frontend.blog.show', $post->slug) }}">
                                    {{ $post->title }}
                                </a>
                            </h2>

                            <p>
                                {{ $post->excerpt ?: str($post->content)->stripTags()->limit(140) }}
                            </p>

                            <a href="{{ route('frontend.blog.show', $post->slug) }}" class="pulse-blog-read">
                                Read post
                                <span class="material-symbols-rounded">arrow_forward</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="pulse-blog-empty">
                        <span class="material-symbols-rounded">edit_note</span>
                        <h2>No published posts</h2>
                        <p>There are no publicly available posts in this archive.</p>
                    </div>
                @endforelse
            </div>

            <div class="pulse-blog-pagination">
                {{ $posts->links() }}
            </div>
        </div>
    </section>
@endsection
