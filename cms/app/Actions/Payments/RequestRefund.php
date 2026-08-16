<?php

namespace App\Actions\Payments;

use App\Domain\Payments\PaymentState;
use App\Domain\Payments\RefundState;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Payments\PaymentGatewayRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RequestRefund
{
    public function __construct(private PaymentGatewayRegistry $gateways) {}

    public function execute(Payment $payment, string $gateway, int $amount, string $reason, string $key, ?User $actor = null): Refund
    {
        $refund = DB::transaction(function () use ($payment, $gateway, $amount, $reason, $key, $actor) {
            $p = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($p->state !== PaymentState::Succeeded || $amount < 1) {
                throw ValidationException::withMessages(['amount_minor' => 'Refund is not allowed.']);
            }$hash = hash('sha256', $key);
            if ($existing = Refund::where('idempotency_key', $hash)->first()) {
                return $existing;
            }$committed = $p->refunds()->whereIn('state', [RefundState::Requested, RefundState::Pending, RefundState::Processing, RefundState::Succeeded])->sum('amount_minor');
            if ($committed + $amount > $p->captured_minor) {
                throw ValidationException::withMessages(['amount_minor' => 'Refund exceeds the remaining captured amount.']);
            }

return $p->refunds()->create(['order_id' => $p->order_id, 'gateway' => $gateway, 'reference' => (string) Str::uuid(), 'amount_minor' => $amount, 'currency' => $p->currency, 'state' => RefundState::Requested, 'reason' => mb_substr(trim($reason), 0, 240), 'actor_id' => $actor?->id, 'idempotency_key' => $hash]);
        }, 5);
        if ($refund->provider_reference) {
            return $refund;
        }$attempt = $payment->attempts()->where('gateway', $gateway)->where('state', PaymentState::Succeeded)->firstOrFail();
        $result = $this->gateways->resolve($gateway)->refund($refund, $attempt->provider_reference);
        $refund->update(['provider_reference' => $result->providerReference, 'state' => $result->state, 'completed_at' => $result->state === RefundState::Succeeded ? now() : null]);
        if ($result->state === RefundState::Succeeded) {
            (new CompleteRefund)->execute($refund);
        }

return $refund->fresh();
    }
}
