<?php

namespace App\Services\Operations;

use App\Domain\Operations\HealthResult;
use App\Domain\Operations\HealthStatus;
use App\Models\OperationalState;
use App\Models\Payment;
use App\Models\PaymentGatewayConfiguration;
use App\Models\PaymentWebhookEvent;
use App\Models\Refund;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class HealthManager
{
    public function results(): array
    {
        return [$this->database(), $this->cache(), $this->storage(), $this->queue(), $this->scheduler(), $this->payments(), $this->runtime()];
    }

    public function status(array $results): HealthStatus
    {
        if (collect($results)->contains(fn ($r) => $r->status === HealthStatus::Unhealthy)) {
            return HealthStatus::Unhealthy;
        }
        if (collect($results)->contains(fn ($r) => in_array($r->status, [HealthStatus::Degraded, HealthStatus::Unknown], true))) {
            return HealthStatus::Degraded;
        }

        return HealthStatus::Healthy;
    }

    private function result(string $key, string $label, HealthStatus $status, string $summary, array $metadata = []): HealthResult
    {
        return new HealthResult($key, $label, $status, $summary, new \DateTimeImmutable, $metadata);
    }

    private function database(): HealthResult
    {
        try {
            DB::select('select 1');

            return $this->result('database', 'Database', HealthStatus::Healthy, 'Database connection is available.');
        } catch (Throwable) {
            return $this->result('database', 'Database', HealthStatus::Unhealthy, 'Database connection is unavailable.');
        }
    }

    private function cache(): HealthResult
    {
        $key = 'pulse:operations:probe:'.bin2hex(random_bytes(8));
        try {
            Cache::put($key, 'ok', 10);
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);

            return $this->result('cache', 'Cache', $ok ? HealthStatus::Healthy : HealthStatus::Unhealthy, $ok ? 'Cache read/write is available.' : 'Cache probe did not round-trip.');
        } catch (Throwable) {
            Cache::forget($key);

            return $this->result('cache', 'Cache', HealthStatus::Unhealthy, 'Cache is unavailable.');
        }
    }

    private function storage(): HealthResult
    {
        $path = 'operations/probes/'.bin2hex(random_bytes(8));
        try {
            Storage::disk('local')->put($path, 'ok');
            $ok = Storage::disk('local')->get($path) === 'ok';

            return $this->result('storage', 'Private storage', $ok ? HealthStatus::Healthy : HealthStatus::Unhealthy, $ok ? 'Private storage is writable.' : 'Storage probe did not round-trip.');
        } catch (Throwable) {
            return $this->result('storage', 'Private storage', HealthStatus::Unhealthy, 'Private storage is unavailable.');
        } finally {
            try {
                Storage::disk('local')->delete($path);
            } catch (Throwable) {
            }
        }
    }

    private function queue(): HealthResult
    {
        try {
            if (config('queue.connections.'.config('queue.default').'.driver') !== 'database') {
                return $this->result('queue', 'Queue', HealthStatus::Unknown, 'Queue metrics are unavailable for this configured driver.', ['driver' => (string) config('queue.connections.'.config('queue.default').'.driver')]);
            } $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            return $this->result('queue', 'Queue', $failed ? HealthStatus::Degraded : HealthStatus::Healthy, $failed ? 'Failed jobs require review.' : 'Queue has no recorded failures.', ['driver' => 'database', 'pending' => $pending, 'failed' => $failed]);
        } catch (Throwable) {
            return $this->result('queue', 'Queue', HealthStatus::Unhealthy, 'Queue storage is unavailable.');
        }
    }

    private function scheduler(): HealthResult
    {
        try {
            $state = OperationalState::where('key', 'scheduler')->first();
            if (! $state?->last_completed_at) {
                return $this->result('scheduler', 'Scheduler', HealthStatus::Unknown, 'No scheduler heartbeat has been recorded.');
            } $age = $state->last_completed_at->diffInSeconds(now());
            $late = (int) config('operations.scheduler_late_seconds', 180);
            $stale = (int) config('operations.scheduler_stale_seconds', 600);

            return $this->result('scheduler', 'Scheduler', $age > $stale ? HealthStatus::Unhealthy : ($age > $late ? HealthStatus::Degraded : HealthStatus::Healthy), $age > $stale ? 'Scheduler heartbeat is stale.' : ($age > $late ? 'Scheduler heartbeat is late.' : 'Scheduler heartbeat is current.'), ['age_seconds' => $age]);
        } catch (Throwable) {
            return $this->result('scheduler', 'Scheduler', HealthStatus::Unknown, 'Scheduler state is unavailable.');
        }
    }

    private function payments(): HealthResult
    {
        try {
            $failed = PaymentWebhookEvent::where('signature_verified', true)->whereNull('processed_at')->count();
            $reconcile = Payment::whereNotNull('reconciliation_required_at')->count();
            $refunds = Refund::where('state', 'pending')->where('created_at', '<', now()->subHour())->count();
            $configured = PaymentGatewayConfiguration::where('enabled', true)->count();

            return $this->result('payments', 'Payments', ($failed + $reconcile + $refunds) > 0 ? HealthStatus::Degraded : HealthStatus::Healthy, ($failed + $reconcile + $refunds) > 0 ? 'Payment operations require attention.' : 'Payment operations have no known backlog.', ['enabled_gateways' => $configured, 'webhook_backlog' => $failed, 'reconciliation_required' => $reconcile, 'aged_pending_refunds' => $refunds]);
        } catch (Throwable) {
            return $this->result('payments', 'Payments', HealthStatus::Unknown, 'Payment metrics are unavailable.');
        }
    }

    private function runtime(): HealthResult
    {
        $ready = filled(config('app.key'));

        return $this->result('runtime', 'Runtime', $ready ? HealthStatus::Healthy : HealthStatus::Unhealthy, $ready ? 'Application encryption is configured.' : 'Application encryption is not configured.', ['session_driver' => (string) config('session.driver'), 'mail_configured' => config('mail.default') !== 'log']);
    }
}
