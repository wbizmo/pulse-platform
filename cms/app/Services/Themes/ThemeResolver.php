<?php

namespace App\Services\Themes;

use App\Domain\Themes\ThemeRegistry;
use App\Domain\Themes\ThemeSettings;
use App\Models\Media;
use App\Models\Theme;

class ThemeResolver
{
    public function __construct(private readonly ThemeRegistry $registry, private readonly ThemeSettings $settings) {}

    public function resolve(?Theme $candidate = null): ThemeRuntime
    {
        $theme = $candidate ?: Theme::query()->whereNotNull('active_slot')->whereNull('retired_at')->first();
        try {
            $manifest = $this->registry->get((string) $theme?->slug);
        } catch (\Throwable) {
            $manifest = $this->registry->get(ThemeRegistry::FALLBACK);
            $theme = Theme::query()->where('slug', ThemeRegistry::FALLBACK)->first() ?: new Theme(['slug' => ThemeRegistry::FALLBACK, 'name' => $manifest['name']]);
        }
        if (! $this->registry->compatible($manifest)) {
            throw new \RuntimeException('No compatible first-party theme manifest is available.');
        }
        $stored = $theme->exists ? $theme->settings()->pluck('value', 'key')->all() : [];
        try {
            $values = $this->settings->validate($manifest['slug'], $stored);
        } catch (\Throwable) {
            $values = $this->settings->defaults($manifest['slug']);
        }
        $ids = array_filter([$values['logo_media_id'], $values['favicon_media_id']]);

        return new ThemeRuntime($theme, $manifest, $values, Media::query()->whereIn('id', $ids)->get()->keyBy('id')->all());
    }
}
