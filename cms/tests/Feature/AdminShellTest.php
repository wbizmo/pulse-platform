<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    private function super(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        return $user;
    }

    public function test_shell_has_accessible_mobile_navigation_and_active_state(): void
    {
        $response = $this->actingAs($this->super())->withSession(['mfa_passed' => true])->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Skip to main content')
            ->assertSee('aria-label="Administration navigation"', false)
            ->assertSee('data-drawer-toggle', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-toast-region', false)
            ->assertSee('pulse-confirm-dialog');
    }

    public function test_flash_toast_escapes_hostile_content(): void
    {
        $response = $this->actingAs($this->super())->withSession([
            'mfa_passed' => true,
            'status' => '<img src=x onerror=alert(1)>',
        ])->get(route('admin.dashboard'));

        $response->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_destructive_user_action_uses_dialog_trigger(): void
    {
        $actor = $this->super();
        User::factory()->create(['name' => 'Delete Candidate']);

        $this->actingAs($actor)->withSession(['mfa_passed' => true])->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="Delete user?"', false);
    }

    public function test_login_errors_have_an_accessible_summary(): void
    {
        $this->followingRedirects()->from(route('admin.login'))
            ->post(route('admin.login.post'), [])
            ->assertOk()->assertSee('role="alert"', false);
    }
}
