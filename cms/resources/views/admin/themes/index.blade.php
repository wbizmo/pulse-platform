@extends('admin.layouts.app', [
    'title' => 'Pulse Themes',
    'heading' => 'Themes',
    'subheading' => 'Manage bundled Pulse themes, activate layouts, customize design, and configure public site behavior.'
])

@section('content')

    <section class="p-module-themes-hero">
        <div>
            <span class="material-symbols-rounded">palette</span>

            <h2>{{ $activeTheme?->name ?? 'No active theme' }}</h2>

            <p>
                The active theme controls your public site structure, default pages,
                layout presets, widget support, header/footer behavior, frontend design,
                and visual customization.
            </p>
        </div>

        <div class="p-module-theme-status">
            <strong>{{ $themes->count() }}</strong>
            <span>Bundled themes</span>
        </div>
    </section>

    <section class="p-module-theme-grid">
        @foreach ($themes as $theme)
            <article class="p-module-theme-card {{ $theme->is_active ? 'active' : '' }}">
                <div class="p-module-theme-preview">
                    <div class="p-module-theme-preview-bar">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <div class="p-module-theme-preview-body">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>

                    @if ($theme->is_active)
                        <span class="p-module-theme-badge">Active</span>
                    @endif
                </div>

                <div class="p-module-theme-content">
                    <div class="p-module-theme-head">
                        <div>
                            <h3>{{ $theme->name }}</h3>
                            <p>{{ ucfirst($theme->category) }} theme</p>
                        </div>

                        <span class="p-module-theme-version">v{{ $theme->version }}</span>
                    </div>

                    <p class="p-module-theme-description">
                        {{ $theme->description }}
                    </p>

                    <div class="p-module-theme-meta">
                        @foreach (($theme->supports ?? []) as $support)
                            <span>{{ str_replace('-', ' ', $support) }}</span>
                        @endforeach
                    </div>

                    <div class="p-module-theme-pages">
                        <strong>Default pages</strong>

                        <div>
                            @foreach (($theme->default_pages ?? []) as $page)
                                <span>{{ $page }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-module-theme-actions">
                        <form method="POST" action="{{ route('admin.themes.activate', $theme) }}">
                            @csrf

                            <button
                                type="submit"
                                class="p-module-btn {{ $theme->is_active ? 'p-module-btn-soft' : 'p-module-btn-dark' }}"
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

                        <a href="{{ route('admin.themes.customizer', $theme) }}" class="p-button p-button--secondary">
                            <span class="material-symbols-rounded">brush</span>
                            Customize
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

@if($history->firstWhere('previous_theme_id'))<x-pulse.card><h2>Activation history</h2><p>Restore the previous compatible theme and its validated settings snapshot.</p><form method="POST" action="{{ route('admin.themes.rollback',$history->firstWhere('previous_theme_id')) }}">@csrf<x-pulse.button type="submit" variant="secondary">Roll back latest activation</x-pulse.button></form></x-pulse.card>@endif
@endsection
