@extends('admin.layouts.app', ['title' => 'Pages', 'heading' => 'Pages'])

@section('content')
    <x-pulse.page-header title="Pages" description="Create, publish, optimize, and visually build site pages.">
        <x-slot:actions><a class="p-button" href="{{ route('admin.pages.create') }}">Create page</a></x-slot:actions>
    </x-pulse.page-header>

    <x-pulse.card>
        @if ($pages->isEmpty())
            <x-pulse.empty title="No pages yet">Create your first page to start building your site.</x-pulse.empty>
        @else
            <x-pulse.table>
                <thead><tr><th>Page</th><th>Status</th><th>Template</th><th>Flags</th><th>Updated</th><th><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td data-label="Page"><strong>{{ $page->title }}</strong><br><span class="p-muted">{{ $page->slug }}</span></td>
                            <td data-label="Status"><x-pulse.badge :variant="$page->status === 'published' ? 'success' : 'neutral'">{{ ucfirst($page->status) }}</x-pulse.badge></td>
                            <td data-label="Template">{{ ucfirst(str_replace('-', ' ', $page->template)) }}</td>
                            <td data-label="Flags">
                                <div class="p-actions">
                                    @if ($page->is_homepage)<x-pulse.badge>Homepage</x-pulse.badge>@endif
                                    @if ($page->is_blog_page)<x-pulse.badge>Blog</x-pulse.badge>@endif
                                    @if ($page->builder_data)<x-pulse.badge>Builder</x-pulse.badge>@endif
                                    @if (! $page->show_header)<x-pulse.badge>No header</x-pulse.badge>@endif
                                    @if (! $page->show_footer)<x-pulse.badge>No footer</x-pulse.badge>@endif
                                </div>
                            </td>
                            <td data-label="Updated">{{ $page->updated_at?->diffForHumans() }}</td>
                            <td data-label="Actions">
                                <x-pulse.action-bar>
                                    <a class="p-button p-button--subtle" href="{{ route('admin.pages.builder', $page) }}">Builder</a>
                                    <a class="p-button p-button--secondary" href="{{ route('admin.pages.edit', $page) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}">@csrf @method('DELETE')
                                        <x-pulse.button variant="danger" data-confirm data-confirm-title="Delete page?" data-confirm-message="This permanently deletes the page and cannot be undone.">Delete</x-pulse.button>
                                    </form>
                                </x-pulse.action-bar>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-pulse.table>
        @endif
        <x-pulse.pagination :paginator="$pages" />
    </x-pulse.card>
@endsection
