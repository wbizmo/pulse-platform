<?php

namespace App\Domain\Plugins;

use InvalidArgumentException;

final class PluginManifestRegistry
{
    public const PULSE_VERSION = '1.0.0';

    private array $manifests;

    public function __construct(?array $manifests = null)
    {
        $this->manifests = $manifests ?? $this->firstParty();
        $this->validate();
    }

    public function all(): array
    {
        return $this->topological($this->manifests);
    }

    public function get(string $slug): array
    {
        return $this->manifests[$slug] ?? throw new InvalidArgumentException('Unknown plugin manifest.');
    }

    public function supports(array $manifest): bool
    {
        return self::satisfies(self::PULSE_VERSION, $manifest['compatibility']['pulse'] ?? '');
    }

    public static function satisfies(string $version, string $constraint): bool
    {
        if (! preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            return false;
        }
        if (preg_match('/^\^(\d+)\.(\d+)\.(\d+)$/', $constraint, $m)) {
            return version_compare($version, "$m[1].$m[2].$m[3]", '>=') && version_compare($version, ((int) $m[1] + 1).'.0.0', '<');
        }

        return preg_match('/^\d+\.\d+\.\d+$/', $constraint) === 1 && version_compare($version, $constraint, '==');
    }

    private function validate(): void
    {
        $allowedContributions = ['dashboard_widgets', 'hooks'];
        foreach ($this->manifests as $key => $manifest) {
            $slug = $manifest['slug'] ?? '';
            if ($key !== $slug || ! preg_match('/^[a-z][a-z0-9-]{1,63}$/', $slug)) {
                throw new InvalidArgumentException('Plugin manifests require a unique canonical slug.');
            }
            if (! preg_match('/^\d+\.\d+\.\d+$/', $manifest['version'] ?? '')) {
                throw new InvalidArgumentException("Plugin [$slug] has an invalid semantic version.");
            }
            if (! $this->supports($manifest)) {
                throw new InvalidArgumentException("Plugin [$slug] is incompatible with Pulse.");
            }
            foreach ($manifest['requires'] ?? [] as $dependency => $constraint) {
                if ($dependency === $slug || ! isset($this->manifests[$dependency])) {
                    throw new InvalidArgumentException("Plugin [$slug] has an invalid dependency.");
                }
                if (! self::satisfies($this->manifests[$dependency]['version'], $constraint)) {
                    throw new InvalidArgumentException("Plugin [$slug] has an incompatible dependency.");
                }
            }
            foreach ($manifest['conflicts'] ?? [] as $conflict) {
                if ($conflict === $slug || ! isset($this->manifests[$conflict])) {
                    throw new InvalidArgumentException("Plugin [$slug] has an invalid conflict.");
                }
            }
            foreach (array_keys($manifest['contributions'] ?? []) as $type) {
                if (! in_array($type, $allowedContributions, true)) {
                    throw new InvalidArgumentException("Plugin [$slug] declares an unknown contribution type.");
                }
            }
            foreach ($manifest['permissions'] ?? [] as $permission) {
                if (! preg_match('/^plugin\.'.preg_quote($slug, '/').'\.[a-z][a-z0-9._-]{1,80}$/', $permission) || str_contains($permission, 'superadmin')) {
                    throw new InvalidArgumentException("Plugin [$slug] declares an unsafe permission.");
                }
            }
        }
        $this->topological($this->manifests);
    }

    private function topological(array $manifests): array
    {
        $ordered = [];
        $visiting = [];
        $visit = function (string $slug) use (&$visit, &$ordered, &$visiting, $manifests): void {
            if (isset($ordered[$slug])) {
                return;
            }
            if (isset($visiting[$slug])) {
                throw new InvalidArgumentException('Plugin dependency cycle detected.');
            }
            $visiting[$slug] = true;
            $dependencies = array_keys($manifests[$slug]['requires'] ?? []);
            sort($dependencies);
            foreach ($dependencies as $dependency) {
                $visit($dependency);
            }
            unset($visiting[$slug]);
            $ordered[$slug] = $manifests[$slug];
        };
        $slugs = array_keys($manifests);
        sort($slugs);
        foreach ($slugs as $slug) {
            $visit($slug);
        }

        return $ordered;
    }

    private function firstParty(): array
    {
        return [
            'editorial-notes' => [
                'slug' => 'editorial-notes', 'name' => 'Editorial Notes', 'version' => '1.0.0',
                'description' => 'Adds a safe, configurable editorial reminder to the dashboard.',
                'compatibility' => ['pulse' => '^1.0.0'], 'requires' => [], 'conflicts' => [],
                'provides' => ['dashboard-widget'], 'permissions' => [],
                'settings' => [
                    'message' => ['type' => 'string', 'max' => 240, 'default' => 'Remember to preview and verify content before publishing.'],
                    'tone' => ['type' => 'enum', 'values' => ['neutral', 'positive'], 'default' => 'neutral'],
                    'show' => ['type' => 'boolean', 'default' => true],
                ],
                'contributions' => ['dashboard_widgets' => [EditorialNoteWidget::class]],
                'runtime' => EditorialNotesRuntime::class,
            ],
            'publishing-insights' => [
                'slug' => 'publishing-insights', 'name' => 'Publishing Insights', 'version' => '1.0.0',
                'description' => 'Adds a bounded publishing summary widget and an explicit dashboard hook.',
                'compatibility' => ['pulse' => '^1.0.0'], 'requires' => ['editorial-notes' => '^1.0.0'], 'conflicts' => [],
                'provides' => ['dashboard-widget', 'dashboard-hook'],
                'permissions' => ['plugin.publishing-insights.view'], 'settings' => [],
                'contributions' => ['dashboard_widgets' => [PublishingInsightsWidget::class], 'hooks' => ['dashboard.loaded' => [PublishingInsightsHook::class]]],
                'runtime' => PublishingInsightsRuntime::class,
            ],
        ];
    }
}
