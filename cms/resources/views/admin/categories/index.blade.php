@extends('admin.layouts.app', ['title' => 'Categories', 'heading' => 'Categories'])

@section('content')
    <x-pulse.page-header title="Categories" description="Create and manage categories used to organize blog posts." />
    <x-pulse.errors />

    <div class="p-editor-grid">
        <x-pulse.card>
            <h2>Existing categories</h2>
            <div class="p-stack">
                @forelse ($categories as $category)
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="p-form p-record">
                        @csrf
                        @method('PUT')
                        <x-pulse.field name="name" id="category-{{ $category->id }}-name" label="Name" :value="$category->name" required />
                        <x-pulse.field name="slug" id="category-{{ $category->id }}-slug" label="Slug" :value="$category->slug" />
                        <x-pulse.textarea name="description" id="category-{{ $category->id }}-description" label="Description" :value="$category->description" rows="3" />
                        <x-pulse.action-bar>
                            <x-pulse.button type="submit">Save category</x-pulse.button>
                            <x-pulse.button type="submit" variant="danger" form="delete-category-{{ $category->id }}" data-confirm data-confirm-title="Delete category?" data-confirm-message="This permanently deletes the category and cannot be undone.">Delete category</x-pulse.button>
                        </x-pulse.action-bar>
                    </form>
                    <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('admin.categories.destroy', $category) }}" hidden>@csrf @method('DELETE')</form>
                @empty
                    <x-pulse.empty title="No categories yet">Create your first blog category.</x-pulse.empty>
                @endforelse
            </div>
        </x-pulse.card>

        <aside>
            <x-pulse.card>
                <h2>Create category</h2>
                <p class="p-muted">Add a new category for blog posts.</p>
                <form method="POST" action="{{ route('admin.categories.store') }}" class="p-form">
                    @csrf
                    <x-pulse.field name="name" id="new-category-name" label="Name" required placeholder="News" />
                    <x-pulse.field name="slug" id="new-category-slug" label="Slug" placeholder="news" />
                    <x-pulse.textarea name="description" id="new-category-description" label="Description" rows="4" />
                    <x-pulse.button type="submit">Create category</x-pulse.button>
                </form>
            </x-pulse.card>
        </aside>
    </div>
@endsection
