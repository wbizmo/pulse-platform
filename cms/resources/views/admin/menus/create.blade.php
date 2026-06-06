@extends('admin.layouts.app', [
    'title' => 'Create Menu',
    'heading' => 'Create Menu',
    'subheading' => 'Create a new navigation menu for your public website.'
])

@section('content')
    @if ($errors->any())
        <div class="pulse-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.menus.store') }}" class="pulse-settings-form">
        @csrf

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Menu Details</h3>
                <p>Choose a menu name, slug, and frontend location.</p>
            </div>

            <div class="pulse-form-grid">
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

                <label class="pulse-toggle-row">
                    <span>Menu active</span>

                    <span class="pulse-switch">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span class="pulse-switch-track">
                            <span class="pulse-switch-thumb"></span>
                        </span>
                    </span>
                </label>
            </div>
        </section>

        <div class="pulse-save-bar">
            <div>
                <strong>Menu Manager</strong>
                <span>Create the menu, then add page or custom URL items.</span>
            </div>

            <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
                <span>Create menu</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
