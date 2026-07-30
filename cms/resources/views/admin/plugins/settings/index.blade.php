@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure bundled plugin options and frontend behavior.'
])

@section('content')

    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}" class="p-module-settings-form">
        @csrf

        <section class="p-card">
            <div class="p-card-head">
                <h3>General Plugin Settings</h3>
                <p>Basic controls for this bundled Pulse CMS plugin.</p>
            </div>

            <div class="p-module-toggle-list">
                <label class="p-module-toggle-row">
                    <span>Show plugin features on frontend where supported</span>

                    <span class="p-module-switch">
                        <input type="checkbox" name="show_on_frontend" value="1" @checked(($settings['show_on_frontend'] ?? '1') == '1')>
                        <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="p-module-toggle-row">
                    <span>Enable plugin helper notices in admin</span>

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
                <span>Save plugin configuration.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
