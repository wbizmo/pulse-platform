@extends('admin.layouts.app', [
    'title' => 'Pulse Categories',
    'heading' => 'Categories',
    'subheading' => 'Create and manage blog categories.'
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
                <h3>Existing Categories</h3>
                <p>Manage categories used to organize blog posts.</p>
            </div>

            <div class="pulse-category-list">
                @forelse ($categories as $category)
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="pulse-category-card">
                        @csrf
                        @method('PUT')

                        <div class="pulse-form-grid">
                            <label>
                                <span>Name</span>
                                <input type="text" name="name" value="{{ $category->name }}" required>
                            </label>

                            <label>
                                <span>Slug</span>
                                <input type="text" name="slug" value="{{ $category->slug }}">
                            </label>

                            <label class="pulse-form-wide">
                                <span>Description</span>
                                <textarea name="description" rows="3">{{ $category->description }}</textarea>
                            </label>
                        </div>

                        <div class="pulse-category-actions">
                            <button type="submit" class="pulse-inline-btn">
                                <span class="material-symbols-rounded">save</span>
                                Save
                            </button>

                            <button
                                type="button"
                                form="delete-category-{{ $category->id }}"
                                formaction="{{ route('admin.categories.destroy', $category) }}"
                                formmethod="POST"
                                class="pulse-danger-btn" data-confirm data-confirm-title="Delete category?" data-confirm-message="This permanently deletes the category and cannot be undone."
                            >
                                Delete
                            </button>
                        </div>
                    </form>
                    <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('admin.categories.destroy', $category) }}" hidden>
                        @csrf
                        @method('DELETE')
                    </form>
                @empty
                    <div class="pulse-empty">
                        <span class="material-symbols-rounded">category</span>
                        <h3>No categories yet</h3>
                        <p>Create your first blog category.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="pulse-editor-side">
            <section class="pulse-panel">
                <div class="pulse-panel-head">
                    <h3>Create Category</h3>
                    <p>Add a new category for blog posts.</p>
                </div>

                <form method="POST" action="{{ route('admin.categories.store') }}" class="pulse-settings-form">
                    @csrf

                    <div class="pulse-form-grid pulse-form-grid-single">
                        <label>
                            <span>Name</span>
                            <input type="text" name="name" required placeholder="News">
                        </label>

                        <label>
                            <span>Slug</span>
                            <input type="text" name="slug" placeholder="news">
                        </label>

                        <label>
                            <span>Description</span>
                            <textarea name="description" rows="4"></textarea>
                        </label>
                    </div>

                    <button type="submit" class="pulse-btn pulse-btn-dark">
                        <span>Create category</span>
                        <span class="material-symbols-rounded">add</span>
                    </button>
                </form>
            </section>
        </aside>
    </div>
@endsection
