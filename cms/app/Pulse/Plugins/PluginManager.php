<?php

namespace App\Pulse\Plugins;

use App\Models\Plugin;
use Illuminate\Support\Collection;

class PluginManager
{
    public function active(): Collection
    {
        return Plugin::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function all(): Collection
    {
        return Plugin::query()
            ->orderBy('name')
            ->get();
    }

    public function has(string $slug): bool
    {
        return Plugin::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();
    }

    public function inactive(string $slug): bool
    {
        return ! $this->has($slug);
    }

    public function find(string $slug): ?Plugin
    {
        return Plugin::query()
            ->where('slug', $slug)
            ->first();
    }

    public function settings(string $slug): array
    {
        $plugin = $this->find($slug);

        if (! $plugin) {
            return [];
        }

        return is_array($plugin->settings)
            ? $plugin->settings
            : [];
    }

    public function setting(string $slug, string $key, mixed $default = null): mixed
    {
        $settings = $this->settings($slug);

        return $settings[$key] ?? $default;
    }
}
