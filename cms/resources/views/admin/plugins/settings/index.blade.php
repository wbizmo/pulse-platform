@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure bundled plugin options and frontend behavior.'
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
                <h3>General Plugin Settings</h3>
                <p>Basic controls for this bundled Pulse CMS plugin.</p>
            </div>

            <div class="pulse-toggle-list">
                <label class="pulse-toggle-row">
                    <span>Show plugin features on frontend where supported</span>

                    <span class="pulse-switch">
                        <input type="checkbox" name="show_on_frontend" value="1" @checked(($settings['show_on_frontend'] ?? '1') == '1')>
                        <span class="pulse-switch-track"><span class="pulse-switch-thumb"></span></span>
                    </span>
                </label>

                <label class="pulse-toggle-row">
                    <span>Enable plugin helper notices in admin</span>

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
                <span>Save plugin configuration.</span>
            </div>

            <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
