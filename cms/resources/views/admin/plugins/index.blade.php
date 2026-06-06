@extends('admin.layouts.app', [
    'title' => 'Pulse Plugins',
    'heading' => 'Plugins',
    'subheading' => 'Activate, deactivate, and review bundled Pulse CMS modules.'
])

@section('content')
    @if (session('success'))
        <div class="pulse-success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <section class="pulse-plugins-hero">
        <div>
            <span class="material-symbols-rounded">extension</span>
            <h2>{{ $activePluginsCount }} active plugins</h2>
            <p>
                Pulse CMS ships with bundled plugins for content, SEO, security,
                payments, mail, ecommerce, exports, analytics, and business site features.
            </p>
        </div>

        <div class="pulse-plugin-status">
            <strong>{{ $pluginsCount }}</strong>
            <span>Bundled modules</span>
        </div>
    </section>

    <section class="pulse-plugin-groups">
        @foreach ($plugins as $category => $group)
            <div class="pulse-plugin-group">
                <div class="pulse-plugin-group-head">
                    <h3>{{ ucfirst(str_replace('-', ' ', $category)) }}</h3>
                    <span>{{ $group->count() }} plugins</span>
                </div>

                <div class="pulse-plugin-grid">
                    @foreach ($group as $plugin)
                        <article class="pulse-plugin-card {{ $plugin->is_active ? 'active' : '' }}">
                            <div class="pulse-plugin-icon">
                                <span class="material-symbols-rounded">{{ $plugin->icon }}</span>
                            </div>

                            <div class="pulse-plugin-body">
                                <div class="pulse-plugin-title">
                                    <div>
                                        <h4>{{ $plugin->name }}</h4>
                                        <p>v{{ $plugin->version }} · {{ $plugin->author }}</p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.plugins.toggle', $plugin) }}">
                                        @csrf

                                        <label class="pulse-switch pulse-plugin-switch">
                                            <input
                                                type="checkbox"
                                                onchange="this.form.submit()"
                                                @checked($plugin->is_active)
                                            >

                                            <span class="pulse-switch-track">
                                                <span class="pulse-switch-thumb"></span>
                                            </span>
                                        </label>
                                    </form>
                                </div>

                                <p class="pulse-plugin-description">
                                    {{ $plugin->description }}
                                </p>

                                <div class="pulse-plugin-tags">
                                    @if ($plugin->is_active)
                                        <span class="active">Active</span>
                                    @else
                                        <span>Inactive</span>
                                    @endif

                                    @if ($plugin->has_settings)
                                        <span>Settings</span>
                                    @endif

                                    @foreach (($plugin->provides ?? []) as $feature)
                                        <span>{{ str_replace('-', ' ', $feature) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endsection
