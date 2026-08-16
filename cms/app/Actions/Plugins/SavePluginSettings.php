<?php

namespace App\Actions\Plugins;

use App\Actions\Access\RecordAudit;
use App\Domain\Plugins\PluginManifestRegistry;
use App\Domain\Plugins\PluginSettingsSchema;
use App\Models\Plugin;
use App\Models\User;
use App\Pulse\Plugins\PluginRuntime;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SavePluginSettings
{
    public function __construct(private PluginManifestRegistry $registry, private PluginSettingsSchema $schema, private PluginRuntime $runtime, private RecordAudit $audit) {}

    public function execute(Plugin $plugin, User $actor, array $input): void
    {
        try {
            $this->registry->get($plugin->slug);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['plugin' => 'Unknown or retired plugin.']);
        }
        $values = $this->schema->validate($plugin->slug, $input);
        DB::transaction(function () use ($plugin, $actor, $values): void {
            foreach ($values as $key => $value) {
                $plugin->settings()->updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]);
            } $this->audit->execute($actor, 'plugin.settings_updated', $plugin, ['slug' => $plugin->slug, 'keys' => array_keys($values)]);
        });
        $this->runtime->forget();
    }
}
