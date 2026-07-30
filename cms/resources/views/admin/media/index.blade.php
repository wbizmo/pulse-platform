@extends('admin.layouts.app', [
    'title' => 'Pulse Media',
    'heading' => 'Media Library',
    'subheading' => 'Upload, manage, search, copy, and delete images, videos, documents, and files.'
])

@section('content')

    <div class="p-module-page-head">
        <div>
            <h2>Media Library</h2>
            <p>Manage uploaded files that can be used by pages, themes, builders, forms, blog posts, and plugins.</p>
        </div>

        <a href="{{ route('admin.media.upload') }}" class="p-button">
            <span class="material-symbols-rounded">upload</span>
            Upload files
        </a>
    </div>

    <form method="GET" action="{{ route('admin.media') }}" class="p-module-media-filter">
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

        <button type="submit" class="p-button">
            <span>Filter</span>
            <span class="material-symbols-rounded">search</span>
        </button>
    </form>

    <section class="p-module-media-grid">
        @forelse ($mediaItems as $media)
            <article class="p-module-media-card">
                <div class="p-module-media-preview">
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

                <div class="p-module-media-body">
                    <h3>{{ $media->name }}</h3>
                    <p>{{ $media->original_name }}</p>

                    <div class="p-module-media-meta">
                        <span>{{ strtoupper($media->extension ?? 'FILE') }}</span>
                        <span>{{ $media->readable_size }}</span>
                        <span>{{ ucfirst($media->type) }}</span>
                    </div>

                    <div class="p-module-media-url">
                        <input type="text" value="{{ url($media->url) }}" readonly onclick="this.select()">
                    </div>

                    <div class="p-module-media-actions">
                        <a href="{{ $media->url }}" target="_blank">
                            <span class="material-symbols-rounded">open_in_new</span>
                            Open
                        </a>

                        <form method="POST" action="{{ route('admin.media.destroy', $media) }}">
                            @csrf
                            @method('DELETE')

                            <button type="button" data-confirm data-confirm-title="Delete media file?" data-confirm-message="This permanently deletes the media record and stored file.">
                                <span class="material-symbols-rounded">delete</span>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="p-empty p-module-media-empty">
                <span class="material-symbols-rounded">perm_media</span>
                <h3>No media uploaded yet</h3>
                <p>Upload images, PDFs, videos, and documents so they can be used across Pulse CMS.</p>
                <a href="{{ route('admin.media.upload') }}" class="p-button">Upload media</a>
            </div>
        @endforelse
    </section>

    <div class="p-module-pagination">
        {{ $mediaItems->links() }}
    </div>
@endsection
