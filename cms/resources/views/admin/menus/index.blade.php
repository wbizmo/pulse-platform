@extends('admin.layouts.app', [
    'title' => 'Pulse Menus',
    'heading' => 'Menus',
    'subheading' => 'Create and manage navigation menus for headers, footers, sidebars, and legal links.'
])

@section('content')

    <div class="p-module-page-head">
        <div>
            <h2>Navigation Menus</h2>
            <p>Menus power frontend navigation, theme headers, footers, and custom link groups.</p>
        </div>

        <a href="{{ route('admin.menus.create') }}" class="p-button">
            <span class="material-symbols-rounded">add</span>
            New menu
        </a>
    </div>

    <section class="p-module-table-card">
        <div class="p-module-table-wrap">
            <table class="p-module-table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Location</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($menus as $menu)
                        <tr>
                            <td>
                                <strong>{{ $menu->name }}</strong>
                                <span>{{ $menu->slug }}</span>
                            </td>

                            <td>{{ ucfirst($menu->location) }}</td>

                            <td>{{ $menu->items_count }}</td>

                            <td>
                                <span class="p-badge {{ $menu->is_active ? 'published' : 'draft' }}">
                                    {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>{{ $menu->updated_at?->diffForHumans() }}</td>

                            <td>
                                <div class="p-module-row-actions">
                                    <a href="{{ route('admin.menus.edit', $menu) }}">Edit</a>

                                    <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="button" data-confirm data-confirm-title="Delete menu?" data-confirm-message="This permanently deletes the menu and all of its items.">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="p-empty">
                                    <span class="material-symbols-rounded">menu_open</span>
                                    <h3>No menus yet</h3>
                                    <p>Create your first menu for the site header, footer, sidebar, or legal links.</p>
                                    <a href="{{ route('admin.menus.create') }}" class="p-button">Create menu</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
