@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure forms, notifications, submissions, and spam protection.'
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
                <h3>Form Configuration</h3>
                <p>Global behavior for forms created through Pulse CMS.</p>
            </div>

            <div class="pulse-form-grid">
                <label>
                    <span>Admin notification email</span>
                    <input type="email" name="admin_email" value="{{ $settings['admin_email'] ?? '' }}">
                </label>

                <label>
                    <span>Default sender name</span>
                    <input type="text" name="sender_name" value="{{ $settings['sender_name'] ?? 'Pulse CMS' }}">
                </label>

                <label class="pulse-form-wide">
                    <span>Success message</span>
                    <textarea rows="4" name="success_message">{{ $settings['success_message'] ?? 'Thank you. Your submission has been received successfully.' }}</textarea>
                </label>
            </div>

            <div class="pulse-toggle-list">
                <label class="pulse-toggle-row">
                    <span>Enable automatic reply emails</span>
                    <span class="pulse-switch">
                        <input type="checkbox" name="auto_reply_enabled" value="1" @checked(($settings['auto_reply_enabled'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="pulse-toggle-row">
                    <span>Enable HTML email templates</span>
                    <span class="pulse-switch">
                        <input type="checkbox" name="html_email_enabled" value="1" @checked(($settings['html_email_enabled'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="pulse-toggle-row">
                    <span>Save submissions to database</span>
                    <span class="pulse-switch">
                        <input type="checkbox" name="save_submissions" value="1" @checked(($settings['save_submissions'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="pulse-toggle-row">
                    <span>Enable spam protection</span>
                    <span class="pulse-switch">
                        <input type="checkbox" name="spam_protection" value="1" @checked(($settings['spam_protection'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>
            </div>
        </section>

        <div class="pulse-save-bar">
            <div>
                <strong>{{ $plugin->name }}</strong>
                <span>Save forms and notification configuration.</span>
            </div>

            <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
