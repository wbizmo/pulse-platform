<?php

namespace Tests\Feature;

use App\Domain\Access\Permission;
use App\Models\Category;
use App\Models\Page;
use App\Models\Permission as PermissionModel;
use App\Models\Post;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function user(string ...$permissions): User
    {
        $u = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $r = Role::create(['name' => 'seo-'.uniqid(), 'label' => 'SEO']);
        $r->permissions()->attach(PermissionModel::whereIn('name', $permissions)->pluck('id'));
        $u->roles()->attach($r);

        return $u;
    }

    public function test_settings_are_authorized_allowlisted_validated_and_audited(): void
    {
        $this->get(route('admin.seo'))->assertRedirect(route('login'));
        $u = $this->user(Permission::ManagePages->value);
        $this->actingAs($u)->withSession(['mfa_passed' => true])->post(route('admin.seo.update'), [])->assertForbidden();
        $a = $this->user(Permission::ManageSeo->value);
        $this->actingAs($a)->withSession(['mfa_passed' => true])->post(route('admin.seo.update'), ['seo_default_title' => 'Safe ✓', 'seo_schema_type' => 'WebSite', 'injected_key' => 'owned'])->assertRedirect();
        $this->assertDatabaseMissing('settings', ['key' => 'injected_key']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'seo.settings_updated']);
        $this->actingAs($a)->withSession(['mfa_passed' => true])->post(route('admin.seo.update'), ['seo_default_title' => ['bad'], 'seo_schema_type' => 'Script', 'seo_robots_content' => "x\0y"])->assertInvalid(['seo_default_title', 'seo_schema_type', 'seo_robots_content']);
    }

    public function test_content_seo_requires_permission_and_safe_canonical(): void
    {
        $p = Page::create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft', 'meta_title' => 'Preserve']);
        $e = $this->user(Permission::ManagePages->value);
        $data = ['title' => 'Changed', 'slug' => 'page', 'status' => 'draft', 'template' => 'default', 'lock_version' => 0, 'meta_title' => 'Forged'];
        $this->actingAs($e)->withSession(['mfa_passed' => true])->put(route('admin.pages.update', $p), $data)->assertInvalid('meta_title');
        $this->assertSame('Preserve', $p->fresh()->meta_title);
        $s = $this->user(Permission::ManagePages->value, Permission::ManageSeo->value);
        $data['canonical_url'] = 'javascript:alert(1)';
        $this->actingAs($s)->withSession(['mfa_passed' => true])->put(route('admin.pages.update', $p), $data)->assertInvalid('canonical_url');
    }

    public function test_robots_preserves_absolute_sitemap(): void
    {
        Setting::setValue('seo_robots_content', "User-agent: *\r\nAllow: /\r\nSitemap: https://cdn.example.test/custom.xml");
        $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->assertContent("User-agent: *\nAllow: /\nSitemap: https://cdn.example.test/custom.xml\n");
    }

    public function test_sitemap_visibility_home_deduplication_and_archives(): void
    {
        Page::create(['title' => 'Home', 'slug' => 'home', 'status' => 'published', 'published_at' => now(), 'is_homepage' => true]);
        Page::create(['title' => 'Draft', 'slug' => 'draft', 'status' => 'draft']);
        $c = Category::create(['name' => 'News', 'slug' => 'news']);
        $t = Tag::create(['name' => 'Release', 'slug' => 'release']);
        $p = Post::create(['title' => 'Public', 'slug' => 'public', 'status' => 'published', 'published_at' => now(), 'category_id' => $c->id]);
        $p->tags()->attach($t);
        $r = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee('/public', false)->assertDontSee('/draft', false)->assertSee('/blog/category/news', false)->assertSee('/blog/tag/release', false);
        $this->assertSame(1, substr_count($r->getContent(), '<loc>'.route('frontend.home').'</loc>'));
    }

    public function test_rendering_escapes_article_schema_pagination_and_noindex(): void
    {
        Page::create(['title' => 'Home', 'slug' => 'home', 'status' => 'published', 'published_at' => now(), 'is_homepage' => true]);
        $u = User::factory()->create(['name' => 'Real Author']);
        Post::create(['user_id' => $u->id, 'title' => '<script>alert(1)</script>', 'slug' => 'safe', 'excerpt' => 'Description', 'status' => 'published', 'published_at' => now()]);
        $this->get('/blog/safe')->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<title><script>', false)->assertSee('content="article"', false)->assertSee('BlogPosting', false);
        $this->get('/blog?page=2')->assertOk()->assertSee(route('frontend.blog').'?page=2', false);
        Setting::setValue('seo_noindex_enabled', '1');
        $this->get('/')->assertSee('content="noindex,follow"', false);
    }
}
