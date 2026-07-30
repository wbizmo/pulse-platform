@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure security rules, login protection, and site hardening.'
])

@section('content')

    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}" class="p-module-settings-form">
        @csrf

        <section class="p-card">
            <div class="p-card-head">
                <h3>Security Configuration</h3>
                <p>Protect your Pulse CMS installation and administrator accounts.</p>
            </div>

            <div class="p-module-form-grid">
                <label>
                    <span>Custom Admin URL</span>
                    <input type="text" name="admin_slug" value="{{ $settings['admin_slug'] ?? 'admin' }}">
                </label>

                <label>
                    <span>Maximum Login Attempts</span>
                    <input type="number" name="max_login_attempts" value="{{ $settings['max_login_attempts'] ?? '5' }}">
                </label>

                <label>
                    <span>Lockout Duration (Minutes)</span>
                    <input type="number" name="lockout_minutes" value="{{ $settings['lockout_minutes'] ?? '15' }}">
                </label>

                <label>
                    <span>Session Lifetime (Minutes)</span>
                    <input type="number" name="session_lifetime" value="{{ $settings['session_lifetime'] ?? '120' }}">
                </label>
            </div>

            <div class="p-module-toggle-list">
                <label class="p-module-toggle-row">
                    <span>Force HTTPS</span>

                    <span class="p-module-switch">
                        <input type="checkbox" name="force_https" value="1" @checked(($settings['force_https'] ?? '0') == '1')>
                        <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="p-module-toggle-row">
                    <span>Enable Security Headers</span>

                    <span class="p-module-switch">
                        <input type="checkbox" name="security_headers" value="1" @checked(($settings['security_headers'] ?? '1') == '1')>
                        <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="p-module-toggle-row">
                    <span>Disable XML-RPC Equivalent Services</span>

                    <span class="p-module-switch">
                        <input type="checkbox" name="disable_xmlrpc" value="1" @checked(($settings['disable_xmlrpc'] ?? '1') == '1')>
                        <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                    </span>
                </label>
            </div>
        </section>

        <div class="p-module-save-bar">
            <div>
                <strong>{{ $plugin->name }}</strong>
                <span>Save security and hardening settings.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
