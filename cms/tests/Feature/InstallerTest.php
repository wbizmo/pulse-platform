<?php

namespace Tests\Feature;

use App\Models\Installation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_seed_is_production_safe(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('roles', ['name' => 'super_admin', 'is_super_admin' => true]);
        $this->assertDatabaseHas('themes', ['slug' => 'pulse-studio']);
    }

    public function test_demo_seed_fails_closed_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $this->expectException(\RuntimeException::class);
        app(DemoDatabaseSeeder::class)->run();
    }

    public function test_installer_creates_only_supplied_administrator_and_durable_lock(): void
    {
        $this->artisan('pulse:install')
            ->expectsQuestion('Administrator name', 'Release Owner')
            ->expectsQuestion('Administrator email', 'owner@example.com')
            ->expectsQuestion('Administrator password (minimum 12 characters, mixed case, number and symbol)', 'Correct-Horse-7!')
            ->expectsQuestion('Confirm administrator password', 'Correct-Horse-7!')
            ->assertSuccessful();

        $user = User::query()->sole();
        $this->assertSame('owner@example.com', $user->email);
        $this->assertTrue($user->isSuperAdministrator());
        $this->assertFalse($user->hasConfirmedMfa());
        $this->assertSame($user->id, Installation::query()->sole()->installed_by);

        $this->artisan('pulse:install')->expectsOutputToContain('already installed')->assertFailed();
    }
}
