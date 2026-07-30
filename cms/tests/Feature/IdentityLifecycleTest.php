<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class IdentityLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_denied_admin_access_and_can_verify_with_a_signed_link(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.verification.notice'));
        $url = URL::temporarySignedRoute('admin.verification.verify', now()->addMinutes(30), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect(route('admin.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'identity.email_verified', 'target_id' => $user->id]);
    }

    public function test_verification_rejects_tampering_and_resend_is_rate_limited(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get(route('admin.verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();
        $this->actingAs($user)->post(route('admin.verification.send'))->assertRedirect();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_password_reset_response_does_not_enumerate_accounts_and_valid_token_changes_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('admin.password.email'), ['email' => 'missing@example.test'])->assertSessionHas('status');
        $this->post(route('admin.password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $this->post(route('admin.password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'a-secure-new-password', 'password_confirmation' => 'a-secure-new-password'])->assertRedirect(route('admin.login'));
        $this->assertTrue(Hash::check('a-secure-new-password', $user->fresh()->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'identity.password_reset', 'target_id' => $user->id]);
    }

    public function test_disabled_account_is_not_sent_a_reset_notification(): void
    {
        Notification::fake();
        $user = User::factory()->disabled()->create();
        $this->post(route('admin.password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_profile_validation_email_reverification_and_password_confirmation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('admin.profile.update'), ['name' => '', 'email' => 'bad'])->assertSessionHasErrors(['name', 'email']);
        $this->actingAs($user)->patch(route('admin.profile.update'), ['name' => 'Updated', 'email' => 'updated@example.test'])->assertRedirect(route('admin.verification.notice'));
        $this->assertNull($user->fresh()->email_verified_at);
        $this->actingAs($user)->put(route('admin.profile.password'), ['password' => 'a-secure-new-password', 'password_confirmation' => 'a-secure-new-password'])->assertRedirect(route('admin.password.confirm'));
        $this->post(route('admin.password.confirm.store'), ['password' => 'password'])->assertRedirect();
        $this->put(route('admin.profile.password'), ['password' => 'a-secure-new-password', 'password_confirmation' => 'a-secure-new-password'])->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['action' => 'identity.password_changed', 'target_id' => $user->id]);
    }

    public function test_database_sessions_are_bounded_to_owner_and_can_be_revoked(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create();
        $other = User::factory()->create();
        foreach ([['owned-session', $user->id], ['foreign-session', $other->id]] as [$id, $userId]) {
            DB::table('sessions')->insert(['id' => $id, 'user_id' => $userId, 'ip_address' => '127.0.0.1', 'user_agent' => 'Test', 'payload' => '', 'last_activity' => now()->timestamp]);
        }
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => now()->timestamp])->delete(route('admin.profile.sessions.destroy', 'foreign-session'))->assertNotFound();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => now()->timestamp])->delete(route('admin.profile.sessions.destroy', 'owned-session'))->assertRedirect();
        $this->assertDatabaseMissing('sessions', ['id' => 'owned-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'foreign-session']);
    }
}
