<?php

namespace Tests\Feature;

use App\Actions\Themes\ActivateTheme;
use App\Actions\Themes\SaveThemeSettings;
use App\Domain\Themes\ThemeRegistry;
use App\Models\Page;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\PulseThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ThemePlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PulseThemeSeeder::class);
    }

    public function test_registry_is_closed_versioned_and_canonical(): void
    {
        $r = app(ThemeRegistry::class);
        $this->assertSame(['pulse-studio', 'pulse-corporate', 'pulse-commerce'], array_keys($r->all()));
        foreach ($r->all() as $m) {
            $this->assertTrue($r->compatible($m));
            $this->assertContains($m['renderer'], ['studio', 'corporate', 'commerce']);
        }
    }

    public function test_settings_reject_unknown_css_malformed_colours_and_forged_media(): void
    {
        $theme = Theme::where('slug', 'pulse-studio')->firstOrFail();
        $actor = User::factory()->create();
        foreach ([['custom_css' => 'body{}'], ['primary_color' => 'javascript:alert(1)'], ['logo_media_id' => '99999']] as $bad) {
            try {
                app(SaveThemeSettings::class)->execute($theme, $bad, $actor);
                $this->fail('Hostile settings accepted');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_activation_is_atomic_audited_and_rollback_restores_snapshot(): void
    {
        $actor = User::factory()->create();
        $studio = Theme::where('slug', 'pulse-studio')->firstOrFail();
        $corporate = Theme::where('slug', 'pulse-corporate')->firstOrFail();
        app(SaveThemeSettings::class)->execute($studio, ['primary_color' => '#112233'], $actor);
        $history = app(ActivateTheme::class)->execute($corporate, $actor);
        $this->assertSame(1, Theme::whereNotNull('active_slot')->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'theme.activated']);
        app(ActivateTheme::class)->execute($studio, $actor, $history);
        $this->assertSame('#112233', $studio->settings()->where('key', 'primary_color')->value('value'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'theme.rolled_back']);
    }

    public function test_failed_activation_preserves_current_theme(): void
    {
        $actor = User::factory()->create();
        $before = Theme::whereNotNull('active_slot')->firstOrFail();
        $bad = Theme::where('slug', 'pulse-corporate')->firstOrFail();
        $bad->update(['version' => '99.0.0']);
        try {
            app(ActivateTheme::class)->execute($bad, $actor);
            $this->fail();
        } catch (ValidationException) {
        }$this->assertTrue($before->fresh()->is_active);
        $this->assertSame('active', $before->fresh()->active_slot);
    }

    public function test_all_three_render_without_commerce_modules(): void
    {
        $page = Page::create(['title' => 'Theme page', 'slug' => 'theme-page', 'template' => 'default', 'status' => 'published', 'published_at' => now(), 'builder_data' => ['schema_version' => 1, 'nodes' => []]]);
        foreach (Theme::whereNull('retired_at')->get() as $theme) {
            DB::table('themes')->update(['is_active' => false, 'active_slot' => null]);
            $theme->update(['is_active' => true, 'active_slot' => 'active']);
            $renderer = str_replace('pulse-', '', $theme->slug);
            $this->get(route('frontend.page', $page->slug))->assertOk()->assertSee('pulse-theme-'.$renderer, false);
        }
    }

    public function test_legacy_seed_rows_are_retired_not_deleted(): void
    {
        $legacy = Theme::create(['name' => 'Pulse Blog', 'slug' => 'pulse-blog']);
        $this->seed(PulseThemeSeeder::class);
        $this->assertNotNull($legacy->fresh()->retired_at);
    }
}
