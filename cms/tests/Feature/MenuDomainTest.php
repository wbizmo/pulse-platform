<?php

namespace Tests\Feature;

use App\Actions\Content\ReorderMenuItems;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MenuDomainTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        return $user;
    }

    private function menu(array $attributes = []): Menu
    {
        return Menu::create($attributes + ['name' => 'Navigation', 'slug' => 'navigation', 'location' => 'custom', 'is_active' => true]);
    }

    public function test_direct_routes_require_authentication_and_mfa(): void
    {
        $menu = $this->menu();
        $this->get(route('admin.menus'))->assertRedirect(route('login'));
        $this->post(route('admin.menus.store'))->assertRedirect(route('login'));
        $this->actingAs($this->actor())->post(route('admin.menus.items.store', $menu))->assertRedirect(route('admin.mfa.challenge'));
    }

    public function test_menu_validation_normalizes_slugs_and_singleton_activation(): void
    {
        $actor = $this->actor();
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.menus.store'), ['name' => ' Main Navigation ', 'location' => 'main', 'is_active' => '1'])->assertRedirect();
        $first = Menu::firstOrFail();
        $this->assertSame('main-navigation', $first->slug);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.menus.store'), ['name' => 'Replacement', 'location' => 'main', 'is_active' => '1'])->assertRedirect();
        $this->assertFalse($first->fresh()->is_active);
        $this->assertSame(1, Menu::where('location', 'main')->where('is_active', true)->count());
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.menus.store'), ['name' => '', 'location' => 'unsafe'])->assertInvalid(['name', 'location']);
    }

    public function test_menu_items_enforce_type_and_safe_url_contracts(): void
    {
        $actor = $this->actor();
        $menu = $this->menu();
        foreach (['javascript:alert(1)', 'data:text/html,x', "https://example.com\r\nX: y", '../admin'] as $url) {
            $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.menus.items.store', $menu), ['label' => 'Bad', 'type' => 'custom', 'url' => $url, 'target' => '_self'])->assertInvalid('url');
        }
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.menus.items.store', $menu), ['label' => 'Safe', 'type' => 'custom', 'url' => '/about', 'target' => '_blank', 'is_active' => '1'])->assertRedirect();
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.menus.items.store', $menu), ['label' => 'Missing', 'type' => 'page', 'target' => '_self'])->assertInvalid('page_id');
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.menus.items.store', $menu), ['label' => 'Mixed', 'type' => 'custom', 'page_id' => 999, 'url' => '/safe', 'target' => '_self'])->assertInvalid('page_id');
        $this->assertDatabaseHas('audit_logs', ['action' => 'menu.item_created']);
    }

    public function test_page_links_follow_slug_and_public_visibility(): void
    {
        $menu = $this->menu(['location' => 'main']);
        $page = Page::create(['title' => 'Visible', 'slug' => 'old', 'status' => 'published', 'published_at' => now()]);
        $item = $menu->items()->create(['page_id' => $page->id, 'label' => '<Visible>', 'type' => 'page', 'target' => '_blank', 'sort_order' => 0, 'is_active' => true]);
        $page->update(['slug' => 'new']);
        $this->assertSame('/new', $item->fresh()->load('page')->href());
        $public = Menu::publicAt('main');
        $this->assertCount(1, $public->items);
        $this->assertSame('noopener noreferrer', $public->items->first()->rel());
        $page->update(['status' => 'draft', 'published_at' => null]);
        $this->assertCount(0, Menu::publicAt('main')->items);
    }

    public function test_reorder_is_atomic_complete_and_parent_scoped(): void
    {
        $actor = $this->actor();
        $menu = $this->menu();
        $one = $menu->items()->create(['label' => 'One', 'type' => 'custom', 'url' => '/one', 'sort_order' => 0]);
        $two = $menu->items()->create(['label' => 'Two', 'type' => 'custom', 'url' => '/two', 'sort_order' => 1]);
        app(ReorderMenuItems::class)->execute($menu, [$two->id, $one->id], $actor);
        $this->assertSame([$two->id, $one->id], $menu->items()->pluck('id')->all());
        try {
            app(ReorderMenuItems::class)->execute($menu, [$one->id, $one->id], $actor);
            $this->fail('Invalid reorder accepted.');
        } catch (ValidationException) {
        }
        $this->assertSame([$two->id, $one->id], $menu->items()->pluck('id')->all());
    }

    public function test_page_deletion_is_dependency_protected_by_application_and_database(): void
    {
        $actor = $this->actor();
        $menu = $this->menu();
        $page = Page::create(['title' => 'Used', 'slug' => 'used']);
        $menu->items()->create(['page_id' => $page->id, 'label' => 'Used', 'type' => 'page', 'sort_order' => 0]);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->delete(route('admin.pages.destroy', $page))->assertInvalid('page');
        $this->expectException(QueryException::class);
        $page->delete();
    }
}
