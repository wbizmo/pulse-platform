@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure analytics integrations and visitor tracking.'
])

@section('content')

    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}" class="p-module-settings-form">
        @csrf

        <section class="p-card">
            <div class="p-card-head">
                <h3>Analytics Integrations</h3>
                <p>Connect Google Analytics, Plausible, and future reporting providers.</p>
            </div>

            <div class="p-module-form-grid">
                <label>
                    <span>Google Analytics ID</span>
                    <input type="text" name="google_analytics_id" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX">
                </label>

                <label>
                    <span>Plausible Domain</span>
                    <input type="text" name="plausible_domain" value="{{ $settings['plausible_domain'] ?? '' }}">
                </label>

                <label>
                    <span>Matomo URL</span>
                    <input type="text" name="matomo_url" value="{{ $settings['matomo_url'] ?? '' }}">
                </label>

                <label>
                    <span>Matomo Site ID</span>
                    <input type="text" name="matomo_site_id" value="{{ $settings['matomo_site_id'] ?? '' }}">
                </label>
            </div>

            <div class="p-module-toggle-list">
                <label class="p-module-toggle-row">
                    <span>Enable cookie consent integration</span>

                    <span class="p-module-switch">
                        <input type="checkbox" name="cookie_consent" value="1" @checked(($settings['cookie_consent'] ?? '1') == '1')>
                        <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="p-module-toggle-row">
                    <span>Enable frontend tracking</span>

                    <span class="p-module-switch">
                        <input type="checkbox" name="enabled" value="1" @checked(($settings['enabled'] ?? '1') == '1')>
                        <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                    </span>
                </label>
            </div>
        </section>

        <div class="p-module-save-bar">
            <div>
                <strong>{{ $plugin->name }}</strong>
                <span>Save analytics integration settings.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
