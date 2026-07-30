@extends('admin.layouts.app', ['title' => 'Posts', 'heading' => 'Posts'])
@section('content')
    <x-pulse.page-header title="Posts" description="Create, publish, categorize, and optimize blog posts.">
        <x-slot:actions><a class="p-button" href="{{ route('admin.posts.create') }}">Create post</a></x-slot:actions>
    </x-pulse.page-header>
    <x-pulse.card>
        @if ($posts->isEmpty())
            <x-pulse.empty title="No posts yet">Create your first blog post to start publishing.</x-pulse.empty>
        @else
            <x-pulse.table>
                <thead><tr><th>Post</th><th>Status</th><th>Category</th><th>Author</th><th>Published</th><th><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr>
                            <td data-label="Post"><strong>{{ $post->title }}</strong><br><span class="p-muted">{{ $post->slug }}</span></td>
                            <td data-label="Status"><x-pulse.badge :variant="$post->status->value === 'published' ? 'success' : 'neutral'">{{ ucfirst($post->status->value) }}</x-pulse.badge></td>
                            <td data-label="Category">{{ $post->category?->name ?? 'Uncategorized' }}</td>
                            <td data-label="Author">{{ $post->author?->name ?? 'System' }}</td>
                            <td data-label="Published">{{ $post->published_at?->format('M d, Y') ?? 'Not published' }}</td>
                            <td data-label="Actions"><x-pulse.action-bar>
                                <a class="p-button p-button--secondary" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.posts.destroy', $post) }}">@csrf @method('DELETE')
                                    <x-pulse.button variant="danger" data-confirm data-confirm-title="Delete post?" data-confirm-message="This permanently deletes the post and cannot be undone.">Delete</x-pulse.button>
                                </form>
                            </x-pulse.action-bar></td>
                        </tr>
                    @endforeach
                </tbody>
            </x-pulse.table>
        @endif
        <x-pulse.pagination :paginator="$posts" />
    </x-pulse.card>
@endsection
