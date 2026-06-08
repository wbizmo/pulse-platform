@extends('admin.layouts.app', [
    'title' => 'Theme Customizer',
    'heading' => 'Theme Customizer',
    'subheading' => $theme->name
])

@section('content')

@if(session('success'))
    <div class="pulse-success">
        <span class="material-symbols-rounded">check_circle</span>
        {{ session('success') }}
    </div>
@endif

<form
    method="POST"
    action="{{ route('admin.themes.customizer.update', $theme) }}"
    class="pulse-settings-form"
>
    @csrf

    <div class="pulse-settings-grid">

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Branding</h3>
                <p>Control visual branding assets.</p>
            </div>

            <div class="pulse-form-grid">
                <label>
                    <span>Logo URL</span>

                    <input
                        type="text"
                        name="logo_url"
                        value="{{ $settings['logo_url'] ?? '' }}"
                    >
                </label>

                <label>
                    <span>Favicon URL</span>

                    <input
                        type="text"
                        name="favicon_url"
                        value="{{ $settings['favicon_url'] ?? '' }}"
                    >
                </label>
            </div>
        </section>

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Colors</h3>
                <p>Theme color configuration.</p>
            </div>

            <div class="pulse-form-grid">

                <label>
                    <span>Primary Color</span>

                    <input
                        type="color"
                        name="primary_color"
                        value="{{ $settings['primary_color'] ?? '#111827' }}"
                    >
                </label>

                <label>
                    <span>Secondary Color</span>

                    <input
                        type="color"
                        name="secondary_color"
                        value="{{ $settings['secondary_color'] ?? '#2563eb' }}"
                    >
                </label>

            </div>
        </section>

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Typography</h3>
            </div>

            <div class="pulse-form-grid">

                <label>
                    <span>Font Family</span>

                    <select name="font_family">
                        <option value="Inter">Inter</option>
                        <option value="Poppins">Poppins</option>
                        <option value="Roboto">Roboto</option>
                        <option value="Montserrat">Montserrat</option>
                    </select>
                </label>

                <label>
                    <span>Button Radius</span>

                    <select name="button_radius">
                        <option value="8px">Small</option>
                        <option value="14px">Medium</option>
                        <option value="24px">Large</option>
                        <option value="999px">Pill</option>
                    </select>
                </label>

            </div>
        </section>

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Layout</h3>
            </div>

            <div class="pulse-form-grid">

                <label>
                    <span>Header Style</span>

                    <select name="header_style">
                        <option value="classic">Classic</option>
                        <option value="centered">Centered</option>
                        <option value="minimal">Minimal</option>
                    </select>
                </label>

                <label>
                    <span>Footer Style</span>

                    <select name="footer_style">
                        <option value="classic">Classic</option>
                        <option value="columns">Columns</option>
                        <option value="minimal">Minimal</option>
                    </select>
                </label>

            </div>
        </section>

        <section class="pulse-panel pulse-form-wide">
            <div class="pulse-panel-head">
                <h3>Custom CSS</h3>
                <p>Additional frontend CSS.</p>
            </div>

            <textarea
                name="custom_css"
                rows="12"
            >{{ $settings['custom_css'] ?? '' }}</textarea>
        </section>

    </div>

    <div class="pulse-save-bar">
        <div>
            <strong>Theme Customizer</strong>
            <span>Save changes to this theme.</span>
        </div>

        <button
            type="submit"
            class="pulse-btn pulse-btn-dark pulse-save-btn"
        >
            Save Theme Settings
        </button>
    </div>

</form>

@endsection
