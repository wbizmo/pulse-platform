@extends('admin.layouts.app', [
    'title' => $theme->name . ' Settings',
    'heading' => $theme->name . ' Settings',
    'subheading' => 'Customize theme colors, layout, homepage, header, footer, and public display behavior.'
])

@section('content')

    <form method="POST" action="{{ route('admin.themes.settings.update', $theme) }}" class="p-module-settings-form">
        @csrf

        <div class="p-module-settings-grid">
            <section class="p-card">
                <div class="p-card-head">
                    <h3>Brand & Colors</h3>
                    <p>Theme-specific branding overrides for the active public website.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Logo URL</span>
                        <input type="text" name="logo_url" value="{{ $settings['logo_url'] ?? '' }}" placeholder="/images/logo.png">
                    </label>

                    <label>
                        <span>Favicon URL</span>
                        <input type="text" name="favicon_url" value="{{ $settings['favicon_url'] ?? '' }}" placeholder="/images/favicon.png">
                    </label>

                    <label>
                        <span>Primary color</span>
                        <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? ($theme->default_settings['primary_color'] ?? '#111827') }}">
                    </label>

                    <label>
                        <span>Accent color</span>
                        <input type="color" name="accent_color" value="{{ $settings['accent_color'] ?? ($theme->default_settings['accent_color'] ?? '#2563eb') }}">
                    </label>

                    <label>
                        <span>Navigation style</span>
                        <select name="navigation_style">
                            @foreach (['classic', 'centered', 'split', 'minimal'] as $style)
                                <option value="{{ $style }}" @selected(($settings['navigation_style'] ?? 'classic') === $style)>
                                    {{ ucfirst($style) }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Layout width</span>
                        <select name="layout_width">
                            @foreach (['standard', 'wide', 'boxed'] as $layout)
                                <option value="{{ $layout }}" @selected(($settings['layout_width'] ?? 'standard') === $layout)>
                                    {{ ucfirst($layout) }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Homepage & Blog</h3>
                    <p>Assign important public-facing pages for frontend rendering.</p>
                </div>

                <div class="p-module-form-grid p-module-form-grid-single">
                    <label>
                        <span>Homepage</span>
                        <select name="homepage_id">
                            <option value="">Use theme default</option>
                            @foreach ($pages as $page)
                                <option value="{{ $page->id }}" @selected(($settings['homepage_id'] ?? '') == $page->id)>
                                    {{ $page->title }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Blog page</span>
                        <select name="blog_page_id">
                            <option value="">Use theme default</option>
                            @foreach ($pages as $page)
                                <option value="{{ $page->id }}" @selected(($settings['blog_page_id'] ?? '') == $page->id)>
                                    {{ $page->title }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="p-module-toggle-list p-module-settings-spacer">
                    <label class="p-module-toggle-row">
                        <span>Use boxed layout</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="boxed_layout" value="1" @checked(($settings['boxed_layout'] ?? '0') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Header Controls</h3>
                    <p>Configure public header behavior for this theme.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Header button label</span>
                        <input type="text" name="header_button_label" value="{{ $settings['header_button_label'] ?? 'Contact us' }}">
                    </label>

                    <label>
                        <span>Header button URL</span>
                        <input type="text" name="header_button_url" value="{{ $settings['header_button_url'] ?? '/contact' }}">
                    </label>
                </div>

                <div class="p-module-toggle-list p-module-settings-spacer">
                    @foreach ([
                        'show_header' => 'Show header',
                        'sticky_header' => 'Sticky header',
                        'show_topbar' => 'Show topbar',
                        'show_social_links' => 'Show social links in header',
                    ] as $key => $label)
                        <label class="p-module-toggle-row">
                            <span>{{ $label }}</span>
                            <span class="p-module-switch">
                                <input type="checkbox" name="{{ $key }}" value="1" @checked(($settings[$key] ?? ($key === 'show_header' ? '1' : '0')) == '1')>
                                <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Footer Controls</h3>
                    <p>Footer content, branding, newsletter, and copyright behavior.</p>
                </div>

                <div class="p-module-form-grid p-module-form-grid-single">
                    <label>
                        <span>Footer tagline</span>
                        <textarea name="footer_tagline" rows="4">{{ $settings['footer_tagline'] ?? 'A flexible CMS for modern websites.' }}</textarea>
                    </label>

                    <label>
                        <span>Copyright text</span>
                        <input type="text" name="copyright_text" value="{{ $settings['copyright_text'] ?? '© ' . date('Y') . ' Pulse CMS. All rights reserved.' }}">
                    </label>
                </div>

                <div class="p-module-toggle-list p-module-settings-spacer">
                    @foreach ([
                        'show_footer' => 'Show footer',
                        'show_footer_branding' => 'Show footer branding',
                        'show_newsletter_box' => 'Show newsletter box',
                    ] as $key => $label)
                        <label class="p-module-toggle-row">
                            <span>{{ $label }}</span>
                            <span class="p-module-switch">
                                <input type="checkbox" name="{{ $key }}" value="1" @checked(($settings[$key] ?? ($key === 'show_footer' ? '1' : '0')) == '1')>
                                <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="p-module-save-bar">
            <div>
                <strong>{{ $theme->name }}</strong>
                <span>Save theme appearance, layout, header, footer, and homepage behavior.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save theme settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
