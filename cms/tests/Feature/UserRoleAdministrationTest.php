<?php

namespace Tests\Feature;

use App\Domain\Access\Permission;
use App\Models\AuditLog;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['mfa_passed' => true]);
    }

    private function super(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        return $user;
    }

    public function test_direct_access_requires_specific_user_permission(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $role = Role::create(['name' => 'dashboard_only', 'label' => 'Dashboard']);
        $role->permissions()->attach(PermissionModel::where('name', Permission::ViewDashboard->value)->first());
        $user->roles()->attach($role);
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_administrator_can_create_and_update_a_user_with_audits(): void
    {
        $actor = $this->super();
        $editor = Role::where('name', 'editor')->first();
        $response = $this->actingAs($actor)->post(route('admin.users.store'), ['name' => 'Casey', 'email' => 'casey@example.test', 'password' => 'correct-horse-battery', 'password_confirmation' => 'correct-horse-battery', 'status' => 'active', 'roles' => [$editor->id]]);
        $response->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'casey@example.test')->firstOrFail();
        $this->assertTrue($user->roles->contains($editor));
        $this->actingAs($actor)->put(route('admin.users.update', $user), ['name' => 'Casey Jones', 'email' => 'casey@example.test', 'status' => 'inactive', 'roles' => [$editor->id]])->assertRedirect(route('admin.users.index'));
        $this->assertSame('inactive', $user->fresh()->status);
        $this->assertSame(2, AuditLog::where('target_type', User::class)->where('target_id', $user->id)->count());
    }

    public function test_validation_rejects_invalid_user_data(): void
    {
        $actor = $this->super();
        $this->actingAs($actor)->post(route('admin.users.store'), ['name' => '', 'email' => 'not-email', 'password' => 'short', 'status' => 'unknown', 'roles' => []])->assertSessionHasErrors(['name', 'email', 'password', 'status', 'roles']);
    }

    public function test_actor_cannot_delegate_permissions_they_do_not_hold(): void
    {
        $actor = User::factory()->create(['status' => 'active']);
        $manager = Role::create(['name' => 'user_manager', 'label' => 'User manager']);
        $manager->permissions()->attach(PermissionModel::where('name', Permission::ManageUsers->value)->first());
        $actor->roles()->attach($manager);
        $target = User::factory()->create(['status' => 'active']);
        $author = Role::where('name', 'author')->first();
        $this->actingAs($actor)->put(route('admin.users.update', $target), ['name' => $target->name, 'email' => $target->email, 'status' => 'active', 'roles' => [$author->id]])->assertSessionHasErrors('roles');
    }

    public function test_final_active_super_administrator_cannot_be_demoted_disabled_or_deleted(): void
    {
        $actor = $this->super();
        $admin = Role::where('name', 'admin')->first();
        $payload = ['name' => $actor->name, 'email' => $actor->email, 'status' => 'inactive', 'roles' => [$admin->id]];
        $this->actingAs($actor)->put(route('admin.users.update', $actor), $payload)->assertSessionHasErrors('roles');
        $this->actingAs($actor)->delete(route('admin.users.destroy', $actor))->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $actor->id, 'status' => 'active']);
    }

    public function test_system_roles_are_immutable_and_custom_roles_are_audited(): void
    {
        $actor = $this->super();
        $permission = Permission::ViewDashboard->value;
        $this->actingAs($actor)->post(route('admin.roles.store'), ['name' => 'reviewer', 'label' => 'Reviewer', 'permissions' => [$permission]])->assertRedirect(route('admin.roles.index'));
        $role = Role::where('name', 'reviewer')->first();
        $this->actingAs($actor)->put(route('admin.roles.update', $role), ['label' => 'Content reviewer', 'permissions' => [$permission]])->assertRedirect(route('admin.roles.index'));
        $this->actingAs($actor)->get(route('admin.roles.edit', Role::where('name', 'admin')->first()))->assertForbidden();
        $this->assertSame(2, AuditLog::where('target_type', Role::class)->where('target_id', $role->id)->count());
    }
}
