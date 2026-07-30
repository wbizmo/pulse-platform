@extends('admin.layouts.app', [
    'title' => 'Theme Customizer',
    'heading' => 'Theme Customizer',
    'subheading' => $theme->name
])

@section('content')
    <form method="POST" action="{{ route('admin.themes.customizer.update', $theme) }}" class="p-module-settings-form">
        @csrf

        <div class="p-module-settings-grid">
            <section class="p-card">
                <div class="p-card-head">
                    <h3>Branding</h3>
                    <p>Control visual branding assets.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Logo URL</span>
                        <input type="text" name="logo_url" value="{{ $settings['logo_url'] ?? '' }}">
                    </label>

                    <label>
                        <span>Favicon URL</span>
                        <input type="text" name="favicon_url" value="{{ $settings['favicon_url'] ?? '' }}">
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Colors</h3>
                    <p>Theme color configuration.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Primary Color</span>
                        <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#111827' }}">
                    </label>

                    <label>
                        <span>Secondary Color</span>
                        <input type="color" name="secondary_color" value="{{ $settings['secondary_color'] ?? '#2563eb' }}">
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Typography</h3>
                    <p>Choose frontend type and button shape.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Font Family</span>
                        <select name="font_family">
                            @foreach (['Inter', 'Poppins', 'Roboto', 'Montserrat'] as $font)
                                <option value="{{ $font }}" @selected(($settings['font_family'] ?? 'Inter') === $font)>
                                    {{ $font }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Button Radius</span>
                        <select name="button_radius">
                            <option value="8px" @selected(($settings['button_radius'] ?? '16px') === '8px')>Small</option>
                            <option value="14px" @selected(($settings['button_radius'] ?? '16px') === '14px')>Medium</option>
                            <option value="24px" @selected(($settings['button_radius'] ?? '16px') === '24px')>Large</option>
                            <option value="999px" @selected(($settings['button_radius'] ?? '16px') === '999px')>Pill</option>
                        </select>
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Layout</h3>
                    <p>Control public header, footer, and utility behavior.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Header Style</span>
                        <select name="header_style">
                            @foreach (['classic', 'centered', 'minimal'] as $style)
                                <option value="{{ $style }}" @selected(($settings['header_style'] ?? 'classic') === $style)>
                                    {{ ucfirst($style) }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Footer Style</span>
                        <select name="footer_style">
                            @foreach (['classic', 'columns', 'minimal'] as $style)
                                <option value="{{ $style }}" @selected(($settings['footer_style'] ?? 'classic') === $style)>
                                    {{ ucfirst($style) }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="p-module-toggle-list p-module-settings-spacer">
                    <label class="p-module-toggle-row">
                        <span>Show public back-to-top button</span>

                        <span class="p-module-switch">
                            <input type="checkbox" name="show_back_to_top" value="1" @checked(($settings['show_back_to_top'] ?? '1') == '1')>
                            <span class="p-module-switch-track">
                                <span class="p-module-switch-thumb"></span>
                            </span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="p-card p-module-form-wide">
                <div class="p-card-head">
                    <h3>Custom CSS</h3>
                    <p>Additional frontend CSS.</p>
                </div>

                <textarea name="custom_css" rows="12">{{ $settings['custom_css'] ?? '' }}</textarea>
            </section>
        </div>

        <div class="p-module-save-bar">
            <div>
                <strong>Theme Customizer</strong>
                <span>Save changes to this theme.</span>
            </div>

            <button type="submit" class="p-button">
                Save Theme Settings
            </button>
        </div>
    </form>
@endsection
