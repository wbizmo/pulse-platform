@extends('admin.layouts.app', [
    'title' => 'Edit Menu',
    'heading' => 'Edit Menu',
    'subheading' => 'Manage menu details and add navigation items.'
])

@section('content')
    <x-pulse.errors />

    <div class="p-module-editor-grid">
        <section class="p-card">
            <div class="p-card-head">
                <h3>Menu Details</h3>
                <p>Update this menu’s name, slug, location, and active state.</p>
            </div>

            <form method="POST" action="{{ route('admin.menus.update', $menu) }}" class="p-module-settings-form">
                @csrf
                @method('PUT')

                <div class="p-module-form-grid">
                    <label>
                        <span>Name</span>
                        <input type="text" name="name" value="{{ old('name', $menu->name) }}" required>
                    </label>

                    <label>
                        <span>Slug</span>
                        <input type="text" name="slug" value="{{ old('slug', $menu->slug) }}">
                    </label>

                    <label>
                        <span>Location</span>
                        <select name="location">
                            @foreach (['main', 'footer', 'legal', 'sidebar', 'custom'] as $location)
                                <option value="{{ $location }}" @selected(old('location', $menu->location) === $location)>
                                    {{ ucfirst($location) }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="p-module-toggle-row">
                        <span>Menu active</span>

                        <span class="p-module-switch">
                            <input type="checkbox" name="is_active" value="1" @checked($menu->is_active)>
                            <span class="p-module-switch-track">
                                <span class="p-module-switch-thumb"></span>
                            </span>
                        </span>
                    </label>
                </div>

                <button type="submit" class="p-button">
                    <span>Save menu</span>
                    <span class="material-symbols-rounded">save</span>
                </button>
            </form>
        </section>

        <aside class="p-module-editor-side">
            <section class="p-card">
                <div class="p-card-head">
                    <h3>Add Menu Item</h3>
                    <p>Add a page link or custom URL to this menu.</p>
                </div>

                <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="p-module-settings-form">
                    @csrf

                    <div class="p-module-form-grid p-module-form-grid-single">
                        <label>
                            <span>Label</span>
                            <input type="text" name="label" required placeholder="About">
                        </label>

                        <label>
                            <span>Type</span>
                            <select name="type">
                                <option value="page">Page</option>
                                <option value="custom">Custom URL</option>
                            </select>
                        </label>

                        <label>
                            <span>Page</span>
                            <select name="page_id">
                                <option value="">Choose page</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page->id }}">{{ $page->title }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Custom URL</span>
                            <input type="text" name="url" placeholder="/about or https://example.com">
                        </label>

                        <label>
                            <span>Target</span>
                            <select name="target">
                                <option value="_self">Same tab</option>
                                <option value="_blank">New tab</option>
                            </select>
                        </label>

                        <label>
                            <span>Sort order</span>
                            <input type="number" name="sort_order" value="0">
                        </label>

                        <label class="p-module-toggle-row">
                            <span>Item active</span>

                            <span class="p-module-switch">
                                <input type="checkbox" name="is_active" value="1" checked>
                                <span class="p-module-switch-track">
                                    <span class="p-module-switch-thumb"></span>
                                </span>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="p-button">
                        <span>Add item</span>
                        <span class="material-symbols-rounded">add</span>
                    </button>
                </form>
            </section>
        </aside>
    </div>

    <section class="p-card p-module-menu-items-panel">
        <div class="p-card-head">
            <h3>Menu Items</h3>
            <p>Items appear according to their sort order. Drag-and-drop ordering comes later.</p>
        </div>

        <div class="p-module-menu-items">
            @forelse ($menu->items as $item)
                <div class="p-module-menu-item">
                    <div>
                        <strong>{{ $item->label }}</strong>
                        <span>{{ $item->type }} · {{ $item->url }}</span>
                    </div>

                    <div class="p-module-menu-item-actions">
                        <span class="p-badge {{ $item->is_active ? 'published' : 'draft' }}">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <form method="POST" action="{{ route('admin.menus.items.destroy', $item) }}">
                            @csrf
                            @method('DELETE')

                            <button type="button" data-confirm data-confirm-title="Delete menu item?" data-confirm-message="This permanently removes the item from this menu.">
                                <span class="material-symbols-rounded">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-empty">
                    <span class="material-symbols-rounded">menu_open</span>
                    <h3>No menu items yet</h3>
                    <p>Add page links or custom URLs to start building this navigation menu.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
