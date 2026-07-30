@extends('admin.layouts.app', [
    'title' => 'Pulse Tags',
    'heading' => 'Tags',
    'subheading' => 'Create and manage blog tags.'
])

@section('content')
    @if (session('success'))
        <div class="pulse-success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="pulse-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="pulse-editor-grid">
        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Existing Tags</h3>
                <p>Manage tags used to describe and group blog posts.</p>
            </div>

            <div class="pulse-category-list">
                @forelse ($tags as $tag)
                    <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="pulse-category-card">
                        @csrf
                        @method('PUT')

                        <div class="pulse-form-grid">
                            <label>
                                <span>Name</span>
                                <input type="text" name="name" value="{{ $tag->name }}" required>
                            </label>

                            <label>
                                <span>Slug</span>
                                <input type="text" name="slug" value="{{ $tag->slug }}">
                            </label>
                        </div>

                        <div class="pulse-category-actions">
                            <button type="submit" class="pulse-inline-btn">
                                <span class="material-symbols-rounded">save</span>
                                Save
                            </button>

                            <button
                                type="button"
                                form="delete-tag-{{ $tag->id }}"
                                formaction="{{ route('admin.tags.destroy', $tag) }}"
                                formmethod="POST"
                                class="pulse-danger-btn" data-confirm data-confirm-title="Delete tag?" data-confirm-message="This permanently deletes the tag and cannot be undone."
                            >
                                Delete
                            </button>
                        </div>
                    </form>
                    <form id="delete-tag-{{ $tag->id }}" method="POST" action="{{ route('admin.tags.destroy', $tag) }}" hidden>
                        @csrf
                        @method('DELETE')
                    </form>
                @empty
                    <div class="pulse-empty">
                        <span class="material-symbols-rounded">sell</span>
                        <h3>No tags yet</h3>
                        <p>Create your first blog tag.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="pulse-editor-side">
            <section class="pulse-panel">
                <div class="pulse-panel-head">
                    <h3>Create Tag</h3>
                    <p>Add a new tag for blog posts.</p>
                </div>

                <form method="POST" action="{{ route('admin.tags.store') }}" class="pulse-settings-form">
                    @csrf

                    <div class="pulse-form-grid pulse-form-grid-single">
                        <label>
                            <span>Name</span>
                            <input type="text" name="name" required placeholder="Laravel">
                        </label>

                        <label>
                            <span>Slug</span>
                            <input type="text" name="slug" placeholder="laravel">
                        </label>
                    </div>

                    <button type="submit" class="pulse-btn pulse-btn-dark">
                        <span>Create tag</span>
                        <span class="material-symbols-rounded">add</span>
                    </button>
                </form>
            </section>
        </aside>
    </div>
@endsection
