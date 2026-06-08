@extends('admin.layouts.app', [
    'title' => 'Pulse Posts',
    'heading' => 'Posts',
    'subheading' => 'Create, manage, publish, and optimize blog posts.'
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
            <h2>Blog Posts</h2>
            <p>Manage drafts, published posts, categories, authors, featured images, and SEO metadata.</p>
        </div>

        <a href="{{ route('admin.posts.create') }}" class="pulse-inline-btn">
            <span class="material-symbols-rounded">add</span>
            New post
        </a>
    </div>

    <section class="pulse-table-card">
        <div class="pulse-table-wrap">
            <table class="pulse-table">
                <thead>
                    <tr>
                        <th>Post</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Published</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>
                                <strong>{{ $post->title }}</strong>
                                <span>{{ $post->slug }}</span>
                            </td>

                            <td>
                                <span class="pulse-status {{ $post->status === 'published' ? 'published' : 'draft' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>

                            <td>{{ $post->category?->name ?? 'Uncategorized' }}</td>

                            <td>{{ $post->author?->name ?? 'System' }}</td>

                            <td>{{ $post->published_at?->format('M d, Y') ?? 'Not published' }}</td>

                            <td>
                                <div class="pulse-row-actions">
                                    <a href="{{ route('admin.posts.edit', $post) }}">Edit</a>

                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="pulse-empty">
                                    <span class="material-symbols-rounded">edit_note</span>
                                    <h3>No posts yet</h3>
                                    <p>Create your first blog post to start publishing content with Pulse CMS.</p>
                                    <a href="{{ route('admin.posts.create') }}" class="pulse-inline-btn">Create post</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pulse-pagination">
            {{ $posts->links() }}
        </div>
    </section>
@endsection
