<?php

namespace App\Console\Commands;

use App\Models\OperationalState;
use App\Models\PaymentGatewayConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class ReconcileConfiguredPayments extends Command
{
    protected $signature = 'operations:reconcile-payments {--batch=25}';

    protected $description = 'Reconcile conservative batches for enabled configured gateways';

    public function handle(): int
    {
        $batch = max(1, min(100, (int) $this->option('batch')));
        $gateways = PaymentGatewayConfiguration::where('enabled', true)->orderBy('gateway')->get()->filter->configured();
        $processed = 0;
        foreach ($gateways as $gateway) {
            Artisan::call('payments:reconcile', ['--gateway' => $gateway->gateway, '--batch' => $batch]);
            $processed++;
        }OperationalState::updateOrCreate(['key' => 'payment-reconciliation'], ['last_started_at' => now(), 'last_completed_at' => now(), 'status' => 'healthy', 'metadata' => ['gateways' => $processed, 'batch' => $batch]]);
        $this->info("Reconciliation completed for {$processed} configured gateway(s).");

        return self::SUCCESS;
    }
}
