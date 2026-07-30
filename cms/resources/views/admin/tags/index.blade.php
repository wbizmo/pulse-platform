@extends('admin.layouts.app', ['title' => 'Tags', 'heading' => 'Tags'])

@section('content')
    <x-pulse.page-header title="Tags" description="Create and manage tags used to describe and group blog posts." />
    <x-pulse.errors />

    <div class="p-editor-grid">
        <x-pulse.card>
            <h2>Existing tags</h2>
            <div class="p-stack">
                @forelse ($tags as $tag)
                    <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="p-form p-record">
                        @csrf
                        @method('PUT')
                        <x-pulse.field name="name" id="tag-{{ $tag->id }}-name" label="Name" :value="$tag->name" required />
                        <x-pulse.field name="slug" id="tag-{{ $tag->id }}-slug" label="Slug" :value="$tag->slug" />
                        <x-pulse.action-bar>
                            <x-pulse.button type="submit">Save tag</x-pulse.button>
                            <x-pulse.button type="submit" variant="danger" form="delete-tag-{{ $tag->id }}" data-confirm data-confirm-title="Delete tag?" data-confirm-message="This permanently deletes the tag and cannot be undone.">Delete tag</x-pulse.button>
                        </x-pulse.action-bar>
                    </form>
                    <form id="delete-tag-{{ $tag->id }}" method="POST" action="{{ route('admin.tags.destroy', $tag) }}" hidden>@csrf @method('DELETE')</form>
                @empty
                    <x-pulse.empty title="No tags yet">Create your first blog tag.</x-pulse.empty>
                @endforelse
            </div>
        </x-pulse.card>

        <aside>
            <x-pulse.card>
                <h2>Create tag</h2>
                <p class="p-muted">Add a new tag for blog posts.</p>
                <form method="POST" action="{{ route('admin.tags.store') }}" class="p-form">
                    @csrf
                    <x-pulse.field name="name" id="new-tag-name" label="Name" required placeholder="Laravel" />
                    <x-pulse.field name="slug" id="new-tag-slug" label="Slug" placeholder="laravel" />
                    <x-pulse.button type="submit">Create tag</x-pulse.button>
                </form>
            </x-pulse.card>
        </aside>
    </div>
@endsection
