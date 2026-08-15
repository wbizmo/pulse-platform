<?php

namespace App\Actions\Themes;

use App\Actions\Access\RecordAudit;
use App\Domain\Themes\ThemeSettings;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveThemeSettings
{
    public function __construct(private ThemeSettings $schema, private RecordAudit $audit) {}

    public function execute(Theme $theme, array $input, User $actor): array
    {
        $values = $this->schema->validate($theme->slug, $input);
        DB::transaction(function () use ($theme, $values, $actor): void {
            $locked = Theme::query()->lockForUpdate()->findOrFail($theme->id);
            foreach ($values as $key => $value) {
                $locked->settings()->updateOrCreate(['key' => $key], ['value' => $value === null ? null : (is_bool($value) ? ($value ? '1' : '0') : (string) $value)]);
            }
            $this->audit->execute($actor, 'theme.settings_updated', $locked, ['keys' => array_keys($values), 'schema_version' => 1]);
        });

        return $values;
    }
}
