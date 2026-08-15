<?php

namespace Tests\Feature;

use App\Domain\Builder\BlockRegistry;
use App\Domain\Builder\BuilderDocument;
use App\Models\BuilderTemplate;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BuilderV4Test extends TestCase
{
    use RefreshDatabase;

    private function actor(bool $authorized = true): User
    {
        $user = User::factory()->create(['status' => 'active']);
        if ($authorized) {
            $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());
        }

        return $user;
    }

    private function page(array $attributes = []): Page
    {
        return Page::create($attributes + ['title' => 'Builder', 'slug' => 'builder', 'status' => 'published', 'published_at' => now(), 'lock_version' => 0]);
    }

    private function node(string $type = 'text', array $props = ['content' => 'Safe text'], array $children = []): array
    {
        return ['id' => (string) Str::uuid(), 'type' => $type, 'props' => $props, 'settings' => [], 'children' => $children];
    }

    private function document(array $nodes = []): array
    {
        return ['schema_version' => 1, 'nodes' => $nodes];
    }

    public function test_valid_versioned_nested_document_and_hostile_schema_rejection(): void
    {
        $validator = app(BuilderDocument::class);
        $valid = $this->document([$this->node('section', ['variant' => 'plain'], [$this->node()])]);
        $this->assertSame($valid, $validator->decode(json_encode($valid)));

        $badDocuments = [
            '{',
            json_encode(['schema_version' => 2, 'nodes' => []]),
            json_encode($this->document([$this->node('html', ['html' => '<script>alert(1)</script>'])])),
            json_encode($this->document([array_merge($this->node(), ['unexpected' => true])])),
            json_encode($this->document([$this->node('text', ['content' => str_repeat('x', 10001)])])),
            json_encode($this->document([$this->node('text', ['content' => 'x'], [$this->node()])])),
            json_encode($this->document([$this->node('cta', ['title' => 'x', 'description' => '', 'button_label' => 'x', 'button_url' => 'javascript:alert(1)'])])),
            json_encode($this->document([$this->node('video', ['url' => 'https://evil.test/embed'])])),
        ];
        foreach ($badDocuments as $bad) {
            try {
                $validator->decode($bad);
                $this->fail('Hostile document accepted.');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_duplicate_ids_depth_and_count_are_bounded(): void
    {
        $validator = app(BuilderDocument::class);
        $node = $this->node();
        foreach ([$this->document([$node, $node]), $this->document(array_fill(0, BlockRegistry::MAX_NODES + 1, $node))] as $bad) {
            $this->expectValidationFailure(fn () => $validator->decode(json_encode($bad)));
        }
        $deep = $this->node('section', ['variant' => 'plain']);
        $deep['children'] = [$this->node('columns', ['layout' => 'equal'], [$this->node('columns', ['layout' => 'equal'], [$this->node('columns', ['layout' => 'equal'], [$this->node()])])])];
        $this->expectValidationFailure(fn () => $validator->decode(json_encode($this->document([$deep]))));
    }

    public function test_save_is_atomic_audited_and_optimistically_locked(): void
    {
        $page = $this->page();
        $actor = $this->actor();
        $document = $this->document([$this->node()]);
        $url = route('admin.pages.builder.update', $page);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post($url, ['builder_data' => json_encode($document), 'lock_version' => 0])->assertSessionHasNoErrors();
        $this->assertSame(1, $page->fresh()->lock_version);
        $this->assertDatabaseHas('audit_logs', ['action' => 'builder.saved', 'target_id' => $page->id]);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post($url, ['builder_data' => json_encode($this->document([])), 'lock_version' => 0])->assertInvalid('lock_version');
        $this->assertCount(1, $page->fresh()->builder_data['nodes']);
    }

    public function test_templates_are_validated_authorized_and_snapshot_based(): void
    {
        $document = $this->document([$this->node()]);
        $this->post(route('admin.builder.templates.store'), ['name' => 'Reusable', 'builder_data' => json_encode($document)])->assertRedirect(route('login'));
        $this->actingAs($this->actor(false))->withSession(['mfa_passed' => true])->post(route('admin.builder.templates.store'), ['name' => 'Reusable', 'builder_data' => json_encode($document)])->assertForbidden();
        $actor = $this->actor();
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.builder.templates.store'), ['name' => 'Reusable', 'builder_data' => json_encode($document)])->assertSessionHasNoErrors();
        $template = BuilderTemplate::firstOrFail();
        $this->assertSame(1, $template->schema_version);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->delete(route('admin.builder.templates.destroy', $template))->assertSessionHasNoErrors();
    }

    public function test_public_renderer_escapes_content_and_fails_closed_for_legacy_html(): void
    {
        $page = $this->page(['builder_data' => $this->document([$this->node('text', ['content' => '<script>alert(1)</script>'])])]);
        $this->get(route('frontend.page', $page->slug))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>', false);
        $page->update(['builder_data' => [['type' => 'html', 'html' => '<script>alert(1)</script>']]]);
        $this->get(route('frontend.page', $page->slug))->assertOk()->assertDontSee('<script>', false);
    }

    public function test_builder_direct_access_identity_and_mfa_matrix(): void
    {
        $page = $this->page();
        $url = route('admin.pages.builder', $page);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($this->actor(false))->withSession(['mfa_passed' => true])->get($url)->assertForbidden();
    }

    public function test_authorized_builder_requires_and_accepts_completed_mfa(): void
    {
        $page = $this->page();
        $actor = $this->actor();
        $this->actingAs($actor)->withSession(['mfa_passed' => false])->get(route('admin.pages.builder', $page))->assertRedirect(route('admin.mfa.challenge'));
        $this->withSession(['mfa_passed' => true])->get(route('admin.pages.builder', $page))->assertOk()->assertSee('Responsive preview controls');
    }

    private function expectValidationFailure(callable $operation): void
    {
        try {
            $operation();
            $this->fail('Expected validation failure.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }
    }
}
