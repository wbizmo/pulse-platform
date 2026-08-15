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
                            <span>New items are appended. Use the accessible order controls below.</span>
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
            <p>Items appear in this order. Use Move up and Move down, then save order.</p>
        </div>

        <form id="menu-order-form" method="POST" action="{{ route('admin.menus.items.reorder', $menu) }}">
            @csrf
            @method('PUT')
        </form>
        <div class="p-module-menu-items" data-menu-order>
            @forelse ($menu->items as $item)
                <div class="p-module-menu-item">
                    <input type="hidden" form="menu-order-form" name="items[]" value="{{ $item->id }}">
                    <div>
                        <strong>{{ $item->label }}</strong>
                        <span>{{ $item->type }} · {{ $item->type === 'page' ? '/'.($item->page?->slug ?? '') : $item->url }}</span>
                    </div>

                    <div class="p-module-menu-item-actions">
                        <span class="p-badge {{ $item->is_active ? 'published' : 'draft' }}">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <button type="button" data-move="up" aria-label="Move {{ $item->label }} up">Move up</button>
                        <button type="button" data-move="down" aria-label="Move {{ $item->label }} down">Move down</button>

                        <details>
                            <summary>Edit</summary>
                            <form method="POST" action="{{ route('admin.menus.items.update', [$menu, $item]) }}" class="p-module-settings-form">
                                @csrf
                                @method('PUT')
                                <label><span>Label</span><input name="label" value="{{ $item->label }}" required></label>
                                <label><span>Type</span><select name="type"><option value="page" @selected($item->type === 'page')>Page</option><option value="custom" @selected($item->type === 'custom')>Custom URL</option></select></label>
                                <label><span>Page</span><select name="page_id"><option value="">Choose page</option>@foreach($pages as $page)<option value="{{ $page->id }}" @selected($item->page_id === $page->id)>{{ $page->title }}</option>@endforeach</select></label>
                                <label><span>Custom URL</span><input name="url" value="{{ $item->url }}"></label>
                                <label><span>Target</span><select name="target"><option value="_self" @selected($item->target === '_self')>Same tab</option><option value="_blank" @selected($item->target === '_blank')>New tab</option></select></label>
                                <label><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> Active</label>
                                <button class="p-button" type="submit">Save item</button>
                            </form>
                        </details>

                        <form method="POST" action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}">
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
        <button class="p-button" form="menu-order-form" type="submit">Save order</button>
    </section>
    <script>
        document.querySelectorAll('[data-move]').forEach((button) => button.addEventListener('click', () => {
            const item = button.closest('.p-module-menu-item');
            const sibling = button.dataset.move === 'up' ? item.previousElementSibling : item.nextElementSibling;
            if (!sibling) return;
            button.dataset.move === 'up' ? item.parentNode.insertBefore(item, sibling) : item.parentNode.insertBefore(sibling, item);
            button.focus();
        }));
    </script>
@endsection
