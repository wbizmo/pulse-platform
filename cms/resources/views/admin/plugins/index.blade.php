@extends('admin.layouts.app', [
    'title' => 'Pulse Plugins',
    'heading' => 'Plugins',
    'subheading' => 'Activate, deactivate, and review bundled Pulse CMS modules.'
])

@section('content')

    <section class="p-module-plugins-hero">
        <div>
            <span class="material-symbols-rounded">extension</span>

            <h2>{{ $activePluginsCount }} active plugins</h2>

            <p>
                Pulse CMS ships with bundled plugins for content, SEO, security,
                payments, mail, ecommerce, exports, analytics, forms,
                business websites, school websites, and site management.
            </p>
        </div>

        <div class="p-module-plugin-status">
            <strong>{{ $pluginsCount }}</strong>
            <span>Bundled modules</span>
        </div>
    </section>

    <section class="p-module-plugin-groups">
        @foreach ($plugins as $category => $group)
            <div class="p-module-plugin-group">

                <div class="p-module-plugin-group-head">
                    <h3>{{ ucfirst(str_replace('-', ' ', $category)) }}</h3>
                    <span>{{ $group->count() }} plugins</span>
                </div>

                <div class="p-module-plugin-grid">
                    @foreach ($group as $plugin)

                        <article class="p-module-plugin-card {{ $plugin->is_active ? 'active' : '' }}">

                            <div class="p-module-plugin-icon">
                                <span class="material-symbols-rounded">
                                    {{ $plugin->icon }}
                                </span>
                            </div>

                            <div class="p-module-plugin-body">

                                <div class="p-module-plugin-title">

                                    <div>
                                        <h4>{{ $plugin->name }}</h4>

                                        <p>
                                            v{{ $plugin->version }}
                                            ·
                                            {{ $plugin->author }}
                                        </p>
                                    </div>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.plugins.toggle', $plugin) }}"
                                    >
                                        @csrf

                                        <label class="p-module-switch p-module-plugin-switch">
                                            <input
                                                type="checkbox"
                                                onchange="this.form.submit()"
                                                @checked($plugin->is_active)
                                            >

                                            <span class="p-module-switch-track">
                                                <span class="p-module-switch-thumb"></span>
                                            </span>
                                        </label>
                                    </form>

                                </div>

                                <p class="p-module-plugin-description">
                                    {{ $plugin->description }}
                                </p>

                                <div class="p-module-plugin-tags">

                                    @if ($plugin->is_active)
                                        <span class="active">
                                            Active
                                        </span>
                                    @else
                                        <span>
                                            Inactive
                                        </span>
                                    @endif

                                    @if ($plugin->has_settings)
                                        <span>
                                            Configurable
                                        </span>
                                    @endif

                                    @foreach (($plugin->provides ?? []) as $feature)
                                        <span>
                                            {{ str_replace('-', ' ', $feature) }}
                                        </span>
                                    @endforeach

                                </div>

                                @if ($plugin->has_settings)

                                    <a
                                        href="{{ route('admin.plugins.settings', $plugin) }}"
                                        class="p-button p-button--secondary"
                                    >
                                        <span class="material-symbols-rounded">
                                            settings
                                        </span>

                                        Settings
                                    </a>

                                @endif

                            </div>

                        </article>

                    @endforeach
                </div>

            </div>
        @endforeach
    </section>
@endsection
