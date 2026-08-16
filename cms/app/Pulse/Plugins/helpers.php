<?php

use App\Pulse\Plugins\PluginRuntime;

if (! function_exists('pulse_plugins')) {
    function pulse_plugins(): PluginRuntime
    {
        return app(PluginRuntime::class);
    }
}
if (! function_exists('plugin_active')) {
    function plugin_active(string $slug): bool
    {
        return isset(pulse_plugins()->active()[$slug]);
    }
}
if (! function_exists('plugin_inactive')) {
    function plugin_inactive(string $slug): bool
    {
        return ! plugin_active($slug);
    }
}
if (! function_exists('plugin_setting')) {
    function plugin_setting(string $slug, string $key, mixed $default = null): mixed
    {
        $entry = pulse_plugins()->active()[$slug] ?? null;

        return $entry ? ($entry['plugin']->settings()->where('key', $key)->value('value') ?? $default) : $default;
    }
}
if (! function_exists('plugin_settings')) {
    function plugin_settings(string $slug): array
    {
        $entry = pulse_plugins()->active()[$slug] ?? null;

        return $entry ? $entry['plugin']->settings()->pluck('value', 'key')->all() : [];
    }
}
