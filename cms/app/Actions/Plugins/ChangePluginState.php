<?php

namespace App\Actions\Plugins;

use App\Actions\Access\RecordAudit;
use App\Domain\Plugins\PluginManifestRegistry;
use App\Domain\Plugins\PluginSettingsSchema;
use App\Models\Permission;
use App\Models\Plugin;
use App\Models\User;
use App\Pulse\Plugins\PluginRuntime;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ChangePluginState
{
    public function __construct(private PluginManifestRegistry $registry, private PluginSettingsSchema $settings, private PluginRuntime $runtime, private RecordAudit $audit) {}

    public function activate(Plugin $plugin, User $actor): void
    {
        $manifest = $this->manifestFor($plugin);
        DB::transaction(function () use ($plugin, $actor, $manifest): void {
            $locked = Plugin::query()->lockForUpdate()->findOrFail($plugin->id);
            $this->settings->validate($locked->slug, $locked->settings()->pluck('value', 'key')->all());
            foreach ($manifest['requires'] as $slug => $constraint) {
                $dependency = Plugin::query()->where('slug', $slug)->where('is_active', true)->first();
                if (! $dependency || ! PluginManifestRegistry::satisfies($dependency->version, $constraint)) {
                    throw ValidationException::withMessages(['plugin' => "Activate dependency [$slug] first."]);
                }
            }
            foreach ($manifest['conflicts'] as $slug) {
                if (Plugin::query()->where('slug', $slug)->where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['plugin' => "Plugin conflicts with active [$slug]."]);
                }
            }
            foreach ($manifest['permissions'] as $name) {
                Permission::query()->firstOrCreate(['name' => $name], ['label' => $manifest['name'].' permission']);
            }
            $locked->update(['is_active' => true]);
            $this->audit->execute($actor, 'plugin.activated', $locked, ['slug' => $locked->slug, 'version' => $locked->version]);
        });
        $this->runtime->forget();
    }

    public function deactivate(Plugin $plugin, User $actor): void
    {
        $this->manifestFor($plugin);
        DB::transaction(function () use ($plugin, $actor): void {
            $locked = Plugin::query()->lockForUpdate()->findOrFail($plugin->id);
            foreach ($this->registry->all() as $slug => $manifest) {
                if (isset($manifest['requires'][$locked->slug]) && Plugin::query()->where('slug', $slug)->where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['plugin' => "Deactivate dependent [$slug] first."]);
                }
            }
            $locked->update(['is_active' => false]);
            $this->audit->execute($actor, 'plugin.deactivated', $locked, ['slug' => $locked->slug, 'version' => $locked->version]);
        });
        $this->runtime->forget();
    }

    private function manifestFor(Plugin $plugin): array
    {
        try {
            $manifest = $this->registry->get($plugin->slug);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['plugin' => 'Unknown or retired plugin.']);
        }
        if ($plugin->version !== $manifest['version'] || ! $this->registry->supports($manifest)) {
            throw ValidationException::withMessages(['plugin' => 'The persisted plugin version is unsupported.']);
        }

        return $manifest;
    }
}
