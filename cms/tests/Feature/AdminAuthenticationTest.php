<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('owner@example.com|127.0.0.1');

        parent::tearDown();
    }

    public function test_active_user_can_log_in_and_session_is_regenerated(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);

        $response = $this->post(route('admin.login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_disabled_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'status' => 'disabled',
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_is_logged_out_when_account_becomes_disabled(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $user->update(['status' => 'disabled']);

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        User::factory()->create(['email' => 'owner@example.com']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.post'), [
                'email' => 'owner@example.com',
                'password' => 'incorrect-password',
            ]);
        }

        $response = $this->post(route('admin.login.post'), [
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
