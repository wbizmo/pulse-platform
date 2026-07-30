@extends('admin.layouts.app', [
    'title' => 'Create Menu',
    'heading' => 'Create Menu',
    'subheading' => 'Create a new navigation menu for your public website.'
])

@section('content')
    <x-pulse.errors />

    <form method="POST" action="{{ route('admin.menus.store') }}" class="p-module-settings-form">
        @csrf

        <section class="p-card">
            <div class="p-card-head">
                <h3>Menu Details</h3>
                <p>Choose a menu name, slug, and frontend location.</p>
            </div>

            <div class="p-module-form-grid">
                <label>
                    <span>Name</span>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Main Navigation">
                </label>

                <label>
                    <span>Slug</span>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="main-navigation">
                </label>

                <label>
                    <span>Location</span>
                    <select name="location">
                        @foreach (['main', 'footer', 'legal', 'sidebar', 'custom'] as $location)
                            <option value="{{ $location }}" @selected(old('location', 'main') === $location)>
                                {{ ucfirst($location) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="p-module-toggle-row">
                    <span>Menu active</span>

                    <span class="p-module-switch">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span class="p-module-switch-track">
                            <span class="p-module-switch-thumb"></span>
                        </span>
                    </span>
                </label>
            </div>
        </section>

        <div class="p-module-save-bar">
            <div>
                <strong>Menu Manager</strong>
                <span>Create the menu, then add page or custom URL items.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Create menu</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
