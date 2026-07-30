@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure mail delivery, sender identity, and email behavior.'
])

@section('content')

    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}" class="p-module-settings-form">
        @csrf

        <div class="p-module-settings-grid">
            <section class="p-card">
                <div class="p-card-head">
                    <h3>Mailer Credentials</h3>
                    <p>Use this section for SMTP or API-based mail providers like Resend.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Provider</span>
                        <select name="provider">
                            @foreach (['smtp', 'resend', 'php_mail'] as $provider)
                                <option value="{{ $provider }}" @selected(($settings['provider'] ?? '') === $provider)>
                                    {{ strtoupper(str_replace('_', ' ', $provider)) }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>API Key</span>
                        <input type="password" name="api_key" value="{{ $settings['api_key'] ?? '' }}" placeholder="Provider API key">
                    </label>

                    <label>
                        <span>SMTP Host</span>
                        <input type="text" name="smtp_host" value="{{ $settings['smtp_host'] ?? '' }}" placeholder="smtp.example.com">
                    </label>

                    <label>
                        <span>SMTP Port</span>
                        <input type="text" name="smtp_port" value="{{ $settings['smtp_port'] ?? '' }}" placeholder="587">
                    </label>

                    <label>
                        <span>SMTP Username</span>
                        <input type="text" name="smtp_username" value="{{ $settings['smtp_username'] ?? '' }}">
                    </label>

                    <label>
                        <span>SMTP Password</span>
                        <input type="password" name="smtp_password" value="{{ $settings['smtp_password'] ?? '' }}">
                    </label>

                    <label>
                        <span>Encryption</span>
                        <select name="smtp_encryption">
                            @foreach (['tls', 'ssl', 'none'] as $encryption)
                                <option value="{{ $encryption }}" @selected(($settings['smtp_encryption'] ?? 'tls') === $encryption)>
                                    {{ strtoupper($encryption) }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Test email recipient</span>
                        <input type="email" name="test_email" value="{{ $settings['test_email'] ?? '' }}">
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Sender Identity</h3>
                    <p>Used for forms, system notifications, registration, and future plugin emails.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>From name</span>
                        <input type="text" name="from_name" value="{{ $settings['from_name'] ?? 'Pulse CMS' }}">
                    </label>

                    <label>
                        <span>From email</span>
                        <input type="email" name="from_email" value="{{ $settings['from_email'] ?? '' }}">
                    </label>

                    <label>
                        <span>Reply-to email</span>
                        <input type="email" name="reply_to_email" value="{{ $settings['reply_to_email'] ?? '' }}">
                    </label>

                    <label>
                        <span>Default admin recipient</span>
                        <input type="email" name="admin_recipient" value="{{ $settings['admin_recipient'] ?? '' }}">
                    </label>
                </div>

                <div class="p-module-toggle-list p-module-settings-spacer">
                    <label class="p-module-toggle-row">
                        <span>Use HTML emails where supported</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="html_email_enabled" value="1" @checked(($settings['html_email_enabled'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>
                </div>
            </section>
        </div>

        <div class="p-module-save-bar">
            <div>
                <strong>{{ $plugin->name }}</strong>
                <span>Save mail provider credentials and sender configuration.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
