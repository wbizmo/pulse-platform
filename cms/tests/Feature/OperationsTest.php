<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\OperationalState;
use App\Models\Role;
use App\Models\User;
use App\Services\Operations\ExportManager;
use App\Services\Operations\Redactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'mfa_confirmed_at' => now(), 'mfa_secret' => 'secret']);
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        return $user;
    }

    public function test_public_liveness_is_minimal_and_operations_are_privileged(): void
    {
        $this->get('/up')->assertOk()->assertDontSee('database')->assertDontSee('Laravel');
        $this->get(route('admin.operations.health'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))->withSession(['mfa_passed' => true])->get(route('admin.operations.health'))->assertForbidden();
        $this->actingAs($this->operator())->withSession(['mfa_passed' => true])->get(route('admin.operations.health'))->assertOk()->assertSee('Detailed health');
    }

    public function test_scheduler_heartbeat_is_persisted_and_detectable(): void
    {
        $this->artisan('operations:heartbeat')->assertSuccessful();
        $this->assertDatabaseHas('operational_states', ['key' => 'scheduler', 'status' => 'healthy']);
        OperationalState::where('key', 'scheduler')->update(['last_completed_at' => now()->subHour()]);
        $this->actingAs($this->operator())->withSession(['mfa_passed' => true])->get(route('admin.operations.health'))->assertOk()->assertSee('Scheduler heartbeat is stale.');
    }

    public function test_redactor_handles_mixed_case_nested_and_inline_secrets(): void
    {
        $redacted = app(Redactor::class)->redact(['nested' => ['Authorization' => 'Bearer abc', 'PaSsWoRd' => 'unsafe'], 'message' => 'client_secret=unsafe Bearer abc.def']);
        $this->assertSame('[REDACTED]', $redacted['nested']['Authorization']);
        $this->assertSame('[REDACTED]', $redacted['nested']['PaSsWoRd']);
        $this->assertStringNotContainsString('unsafe', $redacted['message']);
        $this->assertStringNotContainsString('abc.def', $redacted['message']);
    }

    public function test_log_viewer_rejects_traversal_and_escapes_redacted_content(): void
    {
        file_put_contents(storage_path('logs/laravel-test.log'), "<script>alert(1)</script> password=unsafe\n");
        $response = $this->actingAs($this->operator())->withSession(['mfa_passed' => true])->get(route('admin.operations.logs', ['file' => '../.env']));
        $response->assertOk()->assertDontSee('unsafe')->assertDontSee('<script>', false);
        unlink(storage_path('logs/laravel-test.log'));
    }

    public function test_exports_are_private_bounded_and_formula_safe(): void
    {
        Storage::fake('local');
        $manager = app(ExportManager::class);
        $this->assertSame("'=SUM(1,1)", $manager->cell('=SUM(1,1)'));
        $export = $manager->create($this->operator(), 'audit');
        Storage::disk('local')->assertExists($export->path);
        $this->assertStringStartsWith('operations/exports/', $export->path);
    }

    public function test_audits_are_append_only(): void
    {
        $audit = AuditLog::create(['action' => 'test.created', 'target_type' => 'test']);
        $this->expectException(LogicException::class);
        $audit->delete();
    }
}
