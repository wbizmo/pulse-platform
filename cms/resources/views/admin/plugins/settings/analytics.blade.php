@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure analytics integrations and visitor tracking.'
])

@section('content')
    @if (session('success'))
        <div class="pulse-success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}" class="pulse-settings-form">
        @csrf

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Analytics Integrations</h3>
                <p>Connect Google Analytics, Plausible, and future reporting providers.</p>
            </div>

            <div class="pulse-form-grid">
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

            <div class="pulse-toggle-list">
                <label class="pulse-toggle-row">
                    <span>Enable cookie consent integration</span>

                    <span class="pulse-switch">
                        <input type="checkbox" name="cookie_consent" value="1" @checked(($settings['cookie_consent'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="pulse-toggle-row">
                    <span>Enable frontend tracking</span>

                    <span class="pulse-switch">
                        <input type="checkbox" name="enabled" value="1" @checked(($settings['enabled'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>
            </div>
        </section>

        <div class="pulse-save-bar">
            <div>
                <strong>{{ $plugin->name }}</strong>
                <span>Save analytics integration settings.</span>
            </div>

            <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
