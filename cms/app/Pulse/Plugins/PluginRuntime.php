<?php

namespace App\Pulse\Plugins;

use App\Domain\Plugins\DashboardWidget;
use App\Domain\Plugins\PluginHook;
use App\Domain\Plugins\PluginHookEvent;
use App\Domain\Plugins\PluginManifestRegistry;
use App\Domain\Plugins\PluginSettingsSchema;
use App\Models\Plugin;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PluginRuntime
{
    private ?array $active = null;

    public function __construct(private PluginManifestRegistry $registry, private PluginSettingsSchema $settings) {}

    public function manifests(): array
    {
        return $this->registry->all();
    }

    public function forget(): void
    {
        $this->active = null;
    }

    public function active(): array
    {
        if ($this->active !== null) {
            return $this->active;
        }
        $rows = Plugin::query()->where('is_active', true)->whereIn('slug', array_keys($this->registry->all()))->get()->keyBy('slug');
        $resolved = [];
        foreach ($this->registry->all() as $slug => $manifest) {
            $row = $rows->get($slug);
            if (! $row || $row->version !== $manifest['version'] || ! $this->registry->supports($manifest)) {
                continue;
            }
            $dependenciesReady = collect($manifest['requires'])->every(fn ($constraint, $dependency) => isset($resolved[$dependency]) && PluginManifestRegistry::satisfies($resolved[$dependency]['manifest']['version'], $constraint));
            if ($dependenciesReady) {
                $resolved[$slug] = ['manifest' => $manifest, 'plugin' => $row];
            }
        }

        return $this->active = $resolved;
    }

    public function widgets(): array
    {
        $widgets = [];
        foreach ($this->active() as $slug => $entry) {
            $values = $this->settings->validate($slug, $entry['plugin']->settings()->pluck('value', 'key')->all());
            if (($values['show'] ?? true) === false) {
                continue;
            }
            foreach ($entry['manifest']['contributions']['dashboard_widgets'] ?? [] as $class) {
                try {
                    $widget = app($class);
                    if (! $widget instanceof DashboardWidget) {
                        throw new \LogicException('Invalid dashboard widget contract.');
                    }
                    $widgets[] = $widget->render($values);
                } catch (Throwable $error) {
                    Log::warning('Optional plugin contribution failed.', ['plugin' => $slug, 'type' => 'dashboard_widget', 'error' => $error::class]);
                }
            }
        }

        return $widgets;
    }

    public function dispatch(PluginHookEvent $event): void
    {
        foreach ($this->active() as $slug => $entry) {
            foreach ($entry['manifest']['contributions']['hooks'][$event->name] ?? [] as $class) {
                try {
                    $hook = app($class);
                    if (! $hook instanceof PluginHook) {
                        throw new \LogicException('Invalid plugin hook contract.');
                    }
                    $hook->handle($event);
                } catch (Throwable $error) {
                    Log::warning('Optional plugin contribution failed.', ['plugin' => $slug, 'type' => 'hook', 'event' => $event->name, 'error' => $error::class]);
                }
            }
        }
    }
}
