<?php

namespace Database\Seeders;

use App\Domain\Themes\ThemeRegistry;
use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PulseThemeSeeder extends Seeder
{
    public function run(): void
    {
        $registry = app(ThemeRegistry::class);
        DB::transaction(function () use ($registry): void {
            foreach ($registry->all() as $manifest) {
                Theme::updateOrCreate(['slug' => $manifest['slug']], ['name' => $manifest['name'], 'version' => $manifest['version'], 'author' => 'Pulse CMS', 'description' => $manifest['description'], 'category' => $manifest['category'], 'screenshot' => $manifest['screenshot'], 'supports' => $manifest['capabilities'], 'default_settings' => collect($manifest['settings'])->map(fn ($v) => $v['default'])->all(), 'manifest_version' => 1, 'settings_schema_version' => 1, 'retired_at' => null]);
            }
            Theme::query()->whereNotIn('slug', array_keys($registry->all()))->whereNull('retired_at')->update(['retired_at' => now(), 'is_active' => false, 'active_slot' => null]);
            if (! Theme::query()->whereNotNull('active_slot')->exists()) {
                Theme::query()->where('slug', ThemeRegistry::FALLBACK)->update(['is_active' => true, 'active_slot' => 'active']);
            }
        });
    }
}
