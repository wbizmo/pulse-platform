<?php

namespace Database\Seeders;

use App\Domain\Plugins\PluginManifestRegistry;
use App\Models\Plugin;
use Illuminate\Database\Seeder;

class PulsePluginSeeder extends Seeder
{
    public function run(): void
    {
        $registry = app(PluginManifestRegistry::class);
        foreach ($registry->all() as $manifest) {
            Plugin::query()->updateOrCreate(['slug' => $manifest['slug']], [
                'name' => $manifest['name'], 'version' => $manifest['version'], 'author' => 'Pulse CMS',
                'description' => $manifest['description'], 'category' => 'first-party', 'icon' => 'extension',
                'has_settings' => $manifest['settings'] !== [], 'requires' => $manifest['requires'],
                'provides' => $manifest['provides'], 'permissions' => $manifest['permissions'],
                'settings_schema' => $manifest['settings'],
            ]);
        }
    }
}
