@extends('admin.layouts.app', [
    'title' => 'Pulse Themes',
    'heading' => 'Themes',
    'subheading' => 'Manage bundled Pulse themes, activate layouts, and preview default site structures.'
])

@section('content')
    @if (session('success'))
        <div class="pulse-success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <section class="pulse-themes-hero">
        <div>
            <span class="material-symbols-rounded">palette</span>
            <h2>{{ $activeTheme?->name ?? 'No active theme' }}</h2>
            <p>
                The active theme controls your public site structure, default pages,
                design presets, widget support, and frontend layout direction.
            </p>
        </div>

        <div class="pulse-theme-status">
            <strong>{{ $themes->count() }}</strong>
            <span>Bundled themes</span>
        </div>
    </section>

    <section class="pulse-theme-grid">
        @foreach ($themes as $theme)
            <article class="pulse-theme-card {{ $theme->is_active ? 'active' : '' }}">
                <div class="pulse-theme-preview">
                    <div class="pulse-theme-preview-bar">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <div class="pulse-theme-preview-body">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>

                    @if ($theme->is_active)
                        <span class="pulse-theme-badge">Active</span>
                    @endif
                </div>

                <div class="pulse-theme-content">
                    <div class="pulse-theme-head">
                        <div>
                            <h3>{{ $theme->name }}</h3>
                            <p>{{ ucfirst($theme->category) }} theme</p>
                        </div>

                        <span class="pulse-theme-version">v{{ $theme->version }}</span>
                    </div>

                    <p class="pulse-theme-description">
                        {{ $theme->description }}
                    </p>

                    <div class="pulse-theme-meta">
                        @foreach (($theme->supports ?? []) as $support)
                            <span>{{ str_replace('-', ' ', $support) }}</span>
                        @endforeach
                    </div>

                    <div class="pulse-theme-pages">
                        <strong>Default pages</strong>

                        <div>
                            @foreach (($theme->default_pages ?? []) as $page)
                                <span>{{ $page }}</span>
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.themes.activate', $theme) }}">
                        @csrf

                        <button
                            type="submit"
                            class="pulse-btn {{ $theme->is_active ? 'pulse-btn-soft' : 'pulse-btn-dark' }}"
                            @disabled($theme->is_active)
                        >
                            @if ($theme->is_active)
                                <span>Currently active</span>
                                <span class="material-symbols-rounded">check_circle</span>
                            @else
                                <span>Activate theme</span>
                                <span class="material-symbols-rounded">arrow_forward</span>
                            @endif
                        </button>
                    </form>
                </div>
            </article>
        @endforeach
    </section>
@endsection
