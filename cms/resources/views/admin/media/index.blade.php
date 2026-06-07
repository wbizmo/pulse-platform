@extends('admin.layouts.app', [
    'title' => 'Pulse Media',
    'heading' => 'Media Library',
    'subheading' => 'Upload, manage, search, copy, and delete images, videos, documents, and files.'
])

@section('content')
    @if (session('success'))
        <div class="pulse-success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="pulse-page-head">
        <div>
            <h2>Media Library</h2>
            <p>Manage uploaded files that can be used by pages, themes, builders, forms, blog posts, and plugins.</p>
        </div>

        <a href="{{ route('admin.media.upload') }}" class="pulse-inline-btn">
            <span class="material-symbols-rounded">upload</span>
            Upload files
        </a>
    </div>

    <form method="GET" action="{{ route('admin.media') }}" class="pulse-media-filter">
        <label>
            <span>Search media</span>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search file name or mime type">
        </label>

        <label>
            <span>Type</span>
            <select name="type">
                <option value="">All files</option>
                <option value="image" @selected($activeType === 'image')>Images</option>
                <option value="video" @selected($activeType === 'video')>Videos</option>
                <option value="document" @selected($activeType === 'document')>Documents</option>
                <option value="file" @selected($activeType === 'file')>Other files</option>
            </select>
        </label>

        <button type="submit" class="pulse-btn pulse-btn-dark pulse-media-filter-btn">
            <span>Filter</span>
            <span class="material-symbols-rounded">search</span>
        </button>
    </form>

    <section class="pulse-media-grid">
        @forelse ($mediaItems as $media)
            <article class="pulse-media-card">
                <div class="pulse-media-preview">
                    @if ($media->type === 'image')
                        <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: $media->name }}">
                    @elseif ($media->type === 'video')
                        <span class="material-symbols-rounded">smart_display</span>
                    @elseif ($media->type === 'document')
                        <span class="material-symbols-rounded">picture_as_pdf</span>
                    @else
                        <span class="material-symbols-rounded">draft</span>
                    @endif
                </div>

                <div class="pulse-media-body">
                    <h3>{{ $media->name }}</h3>
                    <p>{{ $media->original_name }}</p>

                    <div class="pulse-media-meta">
                        <span>{{ strtoupper($media->extension ?? 'FILE') }}</span>
                        <span>{{ $media->readable_size }}</span>
                        <span>{{ ucfirst($media->type) }}</span>
                    </div>

                    <div class="pulse-media-url">
                        <input type="text" value="{{ url($media->url) }}" readonly onclick="this.select()">
                    </div>

                    <div class="pulse-media-actions">
                        <a href="{{ $media->url }}" target="_blank">
                            <span class="material-symbols-rounded">open_in_new</span>
                            Open
                        </a>

                        <form method="POST" action="{{ route('admin.media.destroy', $media) }}" onsubmit="return confirm('Delete this media file?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit">
                                <span class="material-symbols-rounded">delete</span>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="pulse-empty pulse-media-empty">
                <span class="material-symbols-rounded">perm_media</span>
                <h3>No media uploaded yet</h3>
                <p>Upload images, PDFs, videos, and documents so they can be used across Pulse CMS.</p>
                <a href="{{ route('admin.media.upload') }}" class="pulse-inline-btn">Upload media</a>
            </div>
        @endforelse
    </section>

    <div class="pulse-pagination">
        {{ $mediaItems->links() }}
    </div>
@endsection
