@extends('admin.layouts.app', [
    'title' => 'Pulse Pages',
    'heading' => 'Pages',
    'subheading' => 'Create, manage, publish, and optimize your site pages.'
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
            <h2>Site Pages</h2>
            <p>Manage regular pages, homepage, blog page, templates, visibility, and SEO metadata.</p>
        </div>

        <a href="{{ route('admin.pages.create') }}" class="pulse-inline-btn">
            <span class="material-symbols-rounded">add</span>
            New page
        </a>
    </div>

    <section class="pulse-table-card">
        <div class="pulse-table-wrap">
            <table class="pulse-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Status</th>
                        <th>Template</th>
                        <th>Flags</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pages as $page)
                        <tr>
                            <td>
                                <strong>{{ $page->title }}</strong>
                                <span>{{ $page->slug }}</span>
                            </td>

                            <td>
                                <span class="pulse-status {{ $page->status === 'published' ? 'published' : 'draft' }}">
                                    {{ ucfirst($page->status) }}
                                </span>
                            </td>

                            <td>{{ ucfirst($page->template) }}</td>

                            <td>
                                <div class="pulse-mini-tags">
                                    @if ($page->is_homepage)
                                        <span>Homepage</span>
                                    @endif

                                    @if ($page->is_blog_page)
                                        <span>Blog</span>
                                    @endif

                                    @if (! $page->show_header)
                                        <span>No header</span>
                                    @endif

                                    @if (! $page->show_footer)
                                        <span>No footer</span>
                                    @endif
                                </div>
                            </td>

                            <td>{{ $page->updated_at?->diffForHumans() }}</td>

                            <td>
                                <div class="pulse-row-actions">
                                    <a href="{{ route('admin.pages.edit', $page) }}">Edit</a>

                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page?')">
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
                                    <span class="material-symbols-rounded">article</span>
                                    <h3>No pages yet</h3>
                                    <p>Create your first page to start building your Pulse CMS site.</p>
                                    <a href="{{ route('admin.pages.create') }}" class="pulse-inline-btn">Create page</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pulse-pagination">
            {{ $pages->links() }}
        </div>
    </section>
@endsection
