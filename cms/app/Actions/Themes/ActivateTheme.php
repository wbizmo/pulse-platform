<?php

namespace App\Actions\Themes;

use App\Actions\Access\RecordAudit;
use App\Domain\Themes\ThemeRegistry;
use App\Domain\Themes\ThemeSettings;
use App\Models\Theme;
use App\Models\ThemeActivation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivateTheme
{
    public function __construct(private ThemeRegistry $registry, private ThemeSettings $settings, private RecordAudit $audit) {}

    public function execute(Theme $theme, User $actor, ?ThemeActivation $rollback = null): ThemeActivation
    {
        $manifest = $this->registry->get($theme->slug);
        if ($theme->retired_at || ! $this->registry->compatible($manifest) || $theme->version !== $manifest['version']) {
            throw ValidationException::withMessages(['theme' => 'This theme version is not compatible.']);
        }

        return DB::transaction(function () use ($theme, $actor, $manifest, $rollback): ThemeActivation {
            $rows = Theme::query()->whereIn('id', array_filter([$theme->id, Theme::query()->whereNotNull('active_slot')->value('id')]))->lockForUpdate()->get()->keyBy('id');
            $next = $rows[$theme->id] ?? throw ValidationException::withMessages(['theme' => 'Theme state changed.']);
            $previous = $rows->firstWhere('active_slot', 'active');
            $nextValues = $rollback ? $this->settings->validate($next->slug, $rollback->previous_settings ?? []) : $this->settings->validate($next->slug, $next->settings()->pluck('value', 'key')->all());
            $previousValues = $previous ? $this->settings->validate($previous->slug, $previous->settings()->pluck('value', 'key')->all()) : null;
            if ($previous && $previous->id !== $next->id) {
                $previous->update(['is_active' => false, 'active_slot' => null]);
            }
            if ($rollback) {
                foreach ($nextValues as $key => $value) {
                    $next->settings()->updateOrCreate(['key' => $key], ['value' => $value === null ? null : (is_bool($value) ? ($value ? '1' : '0') : (string) $value)]);
                }
            }
            $next->update(['is_active' => true, 'active_slot' => 'active', 'manifest_version' => $manifest['manifest_version'], 'settings_schema_version' => $manifest['settings_schema_version']]);
            $history = ThemeActivation::create(['previous_theme_id' => $previous?->id, 'next_theme_id' => $next->id, 'previous_version' => $previous?->version, 'next_version' => $next->version, 'settings_schema_version' => 1, 'previous_settings' => $previousValues, 'next_settings' => $nextValues, 'actor_id' => $actor->id, 'rolled_back_from_id' => $rollback?->id]);
            $this->audit->execute($actor, $rollback ? 'theme.rolled_back' : 'theme.activated', $next, ['from' => $previous?->slug, 'to' => $next->slug, 'history_id' => $history->id]);

            return $history;
        });
    }
}
