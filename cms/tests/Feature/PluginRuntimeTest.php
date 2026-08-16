<?php

namespace Tests\Feature;

use App\Actions\Plugins\ChangePluginState;
use App\Actions\Plugins\SavePluginSettings;
use App\Domain\Plugins\EditorialNoteWidget;
use App\Domain\Plugins\PluginManifestRegistry;
use App\Models\Plugin;
use App\Models\User;
use App\Pulse\Plugins\PluginRuntime;
use Database\Seeders\PulsePluginSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

class PluginRuntimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PulsePluginSeeder::class);
    }

    public function test_registry_is_closed_semantic_and_dependency_ordered(): void
    {
        $registry = app(PluginManifestRegistry::class);
        $this->assertSame(['editorial-notes', 'publishing-insights'], array_keys($registry->all()));
        $this->assertTrue(PluginManifestRegistry::satisfies('1.2.3', '^1.0.0'));
        $this->assertFalse(PluginManifestRegistry::satisfies('2.0.0', '^1.0.0'));
    }

    public function test_invalid_versions_missing_dependencies_cycles_and_unsafe_permissions_fail_closed(): void
    {
        foreach ([
            ['a' => $this->manifest('a', 'invalid')],
            ['a' => $this->manifest('a', '1.0.0', ['missing' => '^1.0.0'])],
            ['a' => $this->manifest('a', '1.0.0', ['b' => '^1.0.0']), 'b' => $this->manifest('b', '1.0.0', ['a' => '^1.0.0'])],
            ['a' => $this->manifest('a', '1.0.0', [], ['users.manage'])],
            ['wrong' => $this->manifest('a')],
        ] as $manifests) {
            try {
                new PluginManifestRegistry($manifests);
                $this->fail('Invalid manifest was accepted.');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_activation_dependency_deactivation_atomicity_settings_and_audits(): void
    {
        $actor = User::factory()->create();
        $notes = Plugin::where('slug', 'editorial-notes')->firstOrFail();
        $insights = Plugin::where('slug', 'publishing-insights')->firstOrFail();
        try {
            app(ChangePluginState::class)->activate($insights, $actor);
            $this->fail();
        } catch (ValidationException) {
        }
        $this->assertFalse($insights->fresh()->is_active);
        app(ChangePluginState::class)->activate($notes, $actor);
        app(ChangePluginState::class)->activate($insights, $actor);
        try {
            app(ChangePluginState::class)->deactivate($notes, $actor);
            $this->fail();
        } catch (ValidationException) {
        }
        $this->assertTrue($notes->fresh()->is_active);
        $this->assertDatabaseHas('permissions', ['name' => 'plugin.publishing-insights.view']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'plugin.activated']);

        app(SavePluginSettings::class)->execute($notes, $actor, ['message' => 'Review carefully', 'tone' => 'positive', 'show' => true]);
        $this->assertSame('Review carefully', $notes->settings()->where('key', 'message')->value('value'));
        foreach ([['unknown' => 'x'], ['message' => '<script>'], ['message' => str_repeat('x', 241)], ['tone' => 'hostile']] as $bad) {
            try {
                app(SavePluginSettings::class)->execute($notes, $actor, $bad);
                $this->fail();
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_optional_contribution_failure_is_isolated_and_redacted(): void
    {
        $actor = User::factory()->create();
        $notes = Plugin::where('slug', 'editorial-notes')->firstOrFail();
        app(ChangePluginState::class)->activate($notes, $actor);
        app()->bind(EditorialNoteWidget::class, fn () => throw new \RuntimeException('secret-value'));
        Log::spy();
        $this->assertSame([], app(PluginRuntime::class)->widgets());
        Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message, $context) => ! str_contains(json_encode($context), 'secret-value') && $context['plugin'] === 'editorial-notes');
    }

    private function manifest(string $slug, string $version = '1.0.0', array $requires = [], array $permissions = []): array
    {
        return ['slug' => $slug, 'name' => ucfirst($slug), 'version' => $version, 'description' => '', 'compatibility' => ['pulse' => '^1.0.0'], 'requires' => $requires, 'conflicts' => [], 'provides' => [], 'permissions' => $permissions, 'settings' => [], 'contributions' => [], 'runtime' => \stdClass::class];
    }
}
