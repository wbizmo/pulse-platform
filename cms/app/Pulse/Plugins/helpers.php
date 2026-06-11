<?php

use App\Pulse\Plugins\PluginManager;

if (! function_exists('pulse_plugins')) {
    function pulse_plugins(): PluginManager
    {
        return app(PluginManager::class);
    }
}

if (! function_exists('plugin_active')) {
    function plugin_active(string $slug): bool
    {
        return pulse_plugins()->has($slug);
    }
}

if (! function_exists('plugin_inactive')) {
    function plugin_inactive(string $slug): bool
    {
        return pulse_plugins()->inactive($slug);
    }
}

if (! function_exists('plugin_setting')) {
    function plugin_setting(string $slug, string $key, mixed $default = null): mixed
    {
        return pulse_plugins()->setting($slug, $key, $default);
    }
}

if (! function_exists('plugin_settings')) {
    function plugin_settings(string $slug): array
    {
        return pulse_plugins()->settings($slug);
    }
}
