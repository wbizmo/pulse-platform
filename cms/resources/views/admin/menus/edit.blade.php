@extends('admin.layouts.app', [
    'title' => 'Edit Menu',
    'heading' => 'Edit Menu',
    'subheading' => 'Manage menu details and add navigation items.'
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
                <h3>Menu Details</h3>
                <p>Update this menu’s name, slug, location, and active state.</p>
            </div>

            <form method="POST" action="{{ route('admin.menus.update', $menu) }}" class="pulse-settings-form">
                @csrf
                @method('PUT')

                <div class="pulse-form-grid">
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

                    <label class="pulse-toggle-row">
                        <span>Menu active</span>

                        <span class="pulse-switch">
                            <input type="checkbox" name="is_active" value="1" @checked($menu->is_active)>
                            <span class="pulse-switch-track">
                                <span class="pulse-switch-thumb"></span>
                            </span>
                        </span>
                    </label>
                </div>

                <button type="submit" class="pulse-btn pulse-btn-dark pulse-menu-save-btn">
                    <span>Save menu</span>
                    <span class="material-symbols-rounded">save</span>
                </button>
            </form>
        </section>

        <aside class="pulse-editor-side">
            <section class="pulse-panel">
                <div class="pulse-panel-head">
                    <h3>Add Menu Item</h3>
                    <p>Add a page link or custom URL to this menu.</p>
                </div>

                <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="pulse-settings-form">
                    @csrf

                    <div class="pulse-form-grid pulse-form-grid-single">
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

                        <label class="pulse-toggle-row">
                            <span>Item active</span>

                            <span class="pulse-switch">
                                <input type="checkbox" name="is_active" value="1" checked>
                                <span class="pulse-switch-track">
                                    <span class="pulse-switch-thumb"></span>
                                </span>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="pulse-btn pulse-btn-dark">
                        <span>Add item</span>
                        <span class="material-symbols-rounded">add</span>
                    </button>
                </form>
            </section>
        </aside>
    </div>

    <section class="pulse-panel pulse-menu-items-panel">
        <div class="pulse-panel-head">
            <h3>Menu Items</h3>
            <p>Items appear according to their sort order. Drag-and-drop ordering comes later.</p>
        </div>

        <div class="pulse-menu-items">
            @forelse ($menu->items as $item)
                <div class="pulse-menu-item">
                    <div>
                        <strong>{{ $item->label }}</strong>
                        <span>{{ $item->type }} · {{ $item->url }}</span>
                    </div>

                    <div class="pulse-menu-item-actions">
                        <span class="pulse-status {{ $item->is_active ? 'published' : 'draft' }}">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <form method="POST" action="{{ route('admin.menus.items.destroy', $item) }}" onsubmit="return confirm('Delete this menu item?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit">
                                <span class="material-symbols-rounded">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="pulse-empty">
                    <span class="material-symbols-rounded">menu_open</span>
                    <h3>No menu items yet</h3>
                    <p>Add page links or custom URLs to start building this navigation menu.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
