@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure security rules, login protection, and site hardening.'
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
                <h3>Security Configuration</h3>
                <p>Protect your Pulse CMS installation and administrator accounts.</p>
            </div>

            <div class="pulse-form-grid">
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

            <div class="pulse-toggle-list">
                <label class="pulse-toggle-row">
                    <span>Force HTTPS</span>

                    <span class="pulse-switch">
                        <input type="checkbox" name="force_https" value="1" @checked(($settings['force_https'] ?? '0') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="pulse-toggle-row">
                    <span>Enable Security Headers</span>

                    <span class="pulse-switch">
                        <input type="checkbox" name="security_headers" value="1" @checked(($settings['security_headers'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="pulse-toggle-row">
                    <span>Disable XML-RPC Equivalent Services</span>

                    <span class="pulse-switch">
                        <input type="checkbox" name="disable_xmlrpc" value="1" @checked(($settings['disable_xmlrpc'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>
            </div>
        </section>

        <div class="pulse-save-bar">
            <div>
                <strong>{{ $plugin->name }}</strong>
                <span>Save security and hardening settings.</span>
            </div>

            <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
