<?php

namespace App\Console\Commands;

use App\Actions\Payments\ConfirmSuccessfulPayment;
use App\Domain\Payments\PaymentState;
use App\Models\PaymentAttempt;
use App\Payments\PaymentGatewayRegistry;
use Illuminate\Console\Command;

final class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--gateway=} {--batch=100}';

    protected $description = 'Reconcile a bounded set of unresolved payment attempts';

    public function handle(PaymentGatewayRegistry $registry, ConfirmSuccessfulPayment $confirm): int
    {
        $gateway = (string) $this->option('gateway');
        if (! in_array($gateway, $registry->slugs(), true)) {
            $this->error('A known --gateway is required.');

            return self::INVALID;
        }$batch = max(1, min(500, (int) $this->option('batch')));
        $attempts = PaymentAttempt::where('gateway', $gateway)->whereIn('state', [PaymentState::Initialized, PaymentState::RequiresAction, PaymentState::Pending])->oldest()->limit($batch)->get();
        $processed = 0;
        foreach ($attempts as $attempt) {
            try {
                $result = $registry->resolve($gateway)->verify($attempt);
                if ($result->state === PaymentState::Succeeded) {
                    $confirm->execute($attempt, (int) $result->amountMinor, (string) $result->currency);
                } elseif ($attempt->state->canTransitionTo($result->state)) {
                    $attempt->update(['state' => $result->state, 'provider_status' => mb_substr((string) $result->providerStatus, 0, 80)]);
                }$processed++;
            } catch (\Throwable) {
                $attempt->payment()->update(['reconciliation_required_at' => now()]);
            }
        }$this->info("Reconciled {$processed} payment attempt(s).");

        return self::SUCCESS;
    }
}
