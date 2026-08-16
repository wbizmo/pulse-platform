<?php

namespace App\Actions\Payments;

use App\Domain\Commerce\OrderState;
use App\Domain\Payments\PaymentState;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Payments\PaymentGatewayRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class InitiatePayment
{
    public function __construct(private PaymentGatewayRegistry $gateways) {}

    public function execute(Order $order, string $gateway): PaymentAttempt
    {
        $available = collect($this->gateways->available($order->currency))->contains('slug', $gateway);
        if (! $available) {
            throw ValidationException::withMessages(['gateway' => 'Payment gateway is unavailable for this order.']);
        }
        $attempt = DB::transaction(function () use ($order, $gateway) {
            $o = Order::lockForUpdate()->findOrFail($order->id);
            if ($o->state !== OrderState::AwaitingPayment) {
                throw ValidationException::withMessages(['order' => 'Order is no longer payable.']);
            }$payment = Payment::lockForUpdate()->firstOrCreate(['order_id' => $o->id], ['amount_minor' => $o->total_minor, 'currency' => $o->currency, 'state' => PaymentState::Initialized]);
            if ($payment->amount_minor !== $o->total_minor || $payment->currency !== $o->currency) {
                throw new \LogicException('Payment snapshot mismatch.');
            }$existing = $payment->attempts()->where('gateway', $gateway)->whereIn('state', [PaymentState::Initialized, PaymentState::RequiresAction, PaymentState::Pending])->latest()->first();
            if ($existing) {
                return $existing;
            }$ref = (string) Str::uuid();

            return $payment->attempts()->create(['gateway' => $gateway, 'reference' => $ref, 'idempotency_key' => hash('sha256', 'payment:'.$ref), 'state' => PaymentState::Initialized, 'initiated_at' => now()]);
        });
        if ($attempt->provider_reference) {
            return $attempt;
        }$result = $this->gateways->resolve($gateway)->initiate($attempt, $order);
        $attempt->update(['provider_reference' => $result->providerReference, 'state' => $result->state, 'provider_status' => mb_substr((string) $result->providerStatus, 0, 80), 'action' => array_filter(['redirect_url' => $result->redirectUrl, 'client_secret' => $result->clientSecret])]);

        return $attempt->fresh();
    }
}
