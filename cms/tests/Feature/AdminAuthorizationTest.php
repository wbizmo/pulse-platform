<?php

namespace Tests\Feature;

use App\Domain\Access\Permission;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['mfa_passed' => true]);
    }

    public function test_authenticated_user_without_roles_is_denied_by_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.system.clear-cache'))->assertForbidden();
    }

    public function test_permission_allows_only_its_routes_and_navigation(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'reader', 'label' => 'Reader']);
        $role->permissions()->attach(PermissionModel::where('name', Permission::ViewDashboard->value)->firstOrFail());
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee(route('admin.dashboard'))
            ->assertDontSee(route('admin.settings'))
            ->assertDontSee(route('admin.plugins'));
        $this->actingAs($user)->get(route('admin.settings'))->assertForbidden();
    }

    public function test_legacy_roles_are_migrated_to_expected_permissions(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $editor->roles()->attach(Role::where('name', 'editor')->firstOrFail());

        $this->actingAs($editor)->get(route('admin.pages'))->assertOk();
        $this->actingAs($editor)->get(route('admin.settings'))->assertForbidden();
    }

    public function test_super_administrator_bypasses_catalogue_permissions(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        $this->actingAs($user)->get(route('admin.settings'))->assertOk();
        $this->actingAs($user)->post(route('admin.system.clear-cache'))->assertRedirect();
    }

    public function test_disabled_super_administrator_is_still_rejected(): void
    {
        $user = User::factory()->disabled()->create();
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
