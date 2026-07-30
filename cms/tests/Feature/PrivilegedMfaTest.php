<?php

namespace Tests\Feature;

use App\Actions\Identity\RecoveryCodes;
use App\Domain\Access\Permission;
use App\Models\AuditLog;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivilegedMfaTest extends TestCase
{
    use RefreshDatabase;

    private function privileged(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $role = Role::create(['name' => 'mfa_test_'.uniqid(), 'label' => 'MFA test']);
        $role->permissions()->attach(PermissionModel::where('name', Permission::ViewDashboard->value)->firstOrFail());
        $user->roles()->attach($role);

        return $user;
    }

    public function test_anonymous_disabled_and_unverified_users_cannot_reach_privileged_capability(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $disabled = $this->privileged(['status' => 'disabled']);
        $this->actingAs($disabled)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $unverified = $this->privileged(['email_verified_at' => null]);
        $this->actingAs($unverified)->get(route('admin.dashboard'))->assertRedirect(route('admin.verification.notice'));
    }

    public function test_privilege_is_permission_based_and_requires_enrollment_then_challenge(): void
    {
        $user = $this->privileged(['mfa_secret' => null, 'mfa_confirmed_at' => null]);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.mfa.show'));
        $user->forceFill(['mfa_secret' => 'JBSWY3DPEHPK3PXP', 'mfa_confirmed_at' => now()])->save();
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.mfa.challenge'));
        $this->withSession(['mfa_passed' => true])->get(route('admin.dashboard'))->assertOk();
    }

    public function test_sensitive_mfa_changes_require_password_confirmation_and_secrets_are_hidden(): void
    {
        $user = User::factory()->create(['mfa_secret' => null, 'mfa_confirmed_at' => null]);
        $this->actingAs($user)->post(route('admin.mfa.enroll'))->assertRedirect(route('admin.password.confirm'));
        $response = $this->withSession(['auth.password_confirmed_at' => now()->timestamp])->post(route('admin.mfa.enroll'));
        $response->assertOk()->assertSee('displayed once');
        $user->refresh();
        $this->assertNotSame('JBSWY3DPEHPK3PXP', $user->getRawOriginal('mfa_secret'));
        $this->assertArrayNotHasKey('mfa_secret', $user->toArray());
        $this->get(route('admin.mfa.show'))->assertDontSee($user->mfa_secret);
    }

    public function test_recovery_codes_are_hashed_single_use_and_never_audited(): void
    {
        $user = $this->privileged();
        $codes = app(RecoveryCodes::class)->generate($user);
        $raw = $user->fresh()->getRawOriginal('mfa_recovery_codes');
        $this->assertStringNotContainsString($codes[0], $raw);
        $this->actingAs($user)->post(route('admin.mfa.verify'), ['code' => $codes[0]])->assertRedirect(route('admin.dashboard'));
        $this->post(route('admin.mfa.verify'), ['code' => $codes[0]])->assertSessionHasErrors('code');
        $this->assertStringNotContainsString($codes[0], AuditLog::query()->get()->toJson());
    }

    public function test_role_change_immediately_changes_mfa_enforcement(): void
    {
        $user = User::factory()->create(['mfa_secret' => null, 'mfa_confirmed_at' => null]);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $role = Role::create(['name' => 'later_privileged', 'label' => 'Later privileged']);
        $role->permissions()->attach(PermissionModel::where('name', Permission::ViewDashboard->value)->firstOrFail());
        $user->roles()->attach($role);
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.mfa.show'));
    }
}
