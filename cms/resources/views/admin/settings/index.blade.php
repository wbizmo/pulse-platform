@extends('admin.layouts.app', [
    'title' => 'Pulse Settings',
    'heading' => 'Settings',
    'subheading' => 'Control your site identity, visibility, contact details, and system behavior.'
])

@section('content')

    <form method="POST" action="{{ route('admin.settings.update') }}" class="p-module-settings-form">
        @csrf

        <div class="p-module-settings-grid">
            <section class="p-card">
                <div class="p-card-head">
                    <h3>Site Identity</h3>
                    <p>Basic information used across your website, themes, SEO, and admin area.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Site name</span>
                        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'Pulse CMS' }}">
                    </label>

                    <label>
                        <span>Tagline</span>
                        <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? 'A flexible Laravel-powered CMS' }}">
                    </label>

                    <label>
                        <span>Logo URL</span>
                        <input type="text" name="site_logo" value="{{ $settings['site_logo'] ?? '' }}" placeholder="/images/logo.png">
                    </label>

                    <label>
                        <span>Favicon URL</span>
                        <input type="text" name="site_favicon" value="{{ $settings['site_favicon'] ?? '' }}" placeholder="/images/favicon.png">
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Contact Details</h3>
                    <p>Information that themes and contact sections can display on the frontend.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Email address</span>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? 'hello@example.com' }}">
                    </label>

                    <label>
                        <span>Phone number</span>
                        <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}">
                    </label>

                    <label class="p-module-form-wide">
                        <span>Business address</span>
                        <textarea name="contact_address" rows="4">{{ $settings['contact_address'] ?? '' }}</textarea>
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Visibility Controls</h3>
                    <p>Use these switches to decide which public-facing contact elements should appear.</p>
                </div>

                <div class="p-module-toggle-list">
                    @php
                        $toggles = [
                            'show_email' => 'Show email address',
                            'show_phone' => 'Show phone number',
                            'show_address' => 'Show address',
                            'show_contact_form' => 'Show contact form',
                            'enable_preloader' => 'Enable site preloader',
                            'maintenance_mode' => 'Maintenance mode',
                        ];
                    @endphp

                    @foreach ($toggles as $key => $label)
                        <label class="p-module-toggle-row">
                            <span>{{ $label }}</span>

                            <span class="p-module-switch">
                                <input
                                    type="checkbox"
                                    name="{{ $key }}"
                                    value="1"
                                    @checked(($settings[$key] ?? '0') == '1')
                                >

                                <span class="p-module-switch-track">
                                    <span class="p-module-switch-thumb"></span>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Social Links</h3>
                    <p>Used by themes, footers, headers, contact blocks, and future widgets.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Facebook</span>
                        <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}">
                    </label>

                    <label>
                        <span>X / Twitter</span>
                        <input type="url" name="social_x" value="{{ $settings['social_x'] ?? '' }}">
                    </label>

                    <label>
                        <span>Instagram</span>
                        <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}">
                    </label>

                    <label>
                        <span>LinkedIn</span>
                        <input type="url" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}">
                    </label>

                    <label>
                        <span>YouTube</span>
                        <input type="url" name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}">
                    </label>

                    <label>
                        <span>GitHub</span>
                        <input type="url" name="social_github" value="{{ $settings['social_github'] ?? '' }}">
                    </label>
                </div>
            </section>
        </div>

        <div class="p-module-save-bar">
            <div>
                <strong>Settings Engine</strong>
                <span>Changes are saved to the database and can be used by themes, plugins, and frontend templates.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
