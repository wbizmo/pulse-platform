<?php

namespace App\Payments;

use App\Domain\Payments\PaymentResult;
use App\Domain\Payments\RefundResult;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Refund;

final class StripeGateway extends AbstractHttpGateway
{
    private function request()
    {
        return $this->http()->asForm()->withToken($this->configuration->secret)->baseUrl('https://api.stripe.com/v1');
    }

    public function initiate(PaymentAttempt $a, Order $o): PaymentResult
    {
        $d = $this->request()->withHeader('Idempotency-Key', $a->idempotency_key)->post('/payment_intents', ['amount' => $o->total_minor, 'currency' => strtolower($o->currency), 'metadata[order_reference]' => $o->public_reference, 'automatic_payment_methods[enabled]' => 'true'])->throw()->json();

        return new PaymentResult($this->status($this->string($d, 'status', 80)), $this->string($d, 'id'), $d['status'], $d['next_action']['redirect_to_url']['url'] ?? null, $d['client_secret'] ?? null, (int) ($d['amount_received'] ?? $d['amount'] ?? 0), strtoupper((string) ($d['currency'] ?? '')));
    }

    public function verify(PaymentAttempt $a): PaymentResult
    {
        $d = $this->request()->get('/payment_intents/'.rawurlencode((string) $a->provider_reference))->throw()->json();

        return new PaymentResult($this->status($this->string($d, 'status', 80)), $this->string($d, 'id'), $d['status'], null, null, (int) ($d['amount_received'] ?? 0), strtoupper((string) ($d['currency'] ?? '')));
    }

    public function refund(Refund $r, string $p): RefundResult
    {
        $d = $this->request()->withHeader('Idempotency-Key', $r->idempotency_key)->post('/refunds', ['payment_intent' => $p, 'amount' => $r->amount_minor, 'metadata[refund_reference]' => $r->reference])->throw()->json();

        return new RefundResult($this->refundStatus($this->string($d, 'status', 80)), $this->string($d, 'id'), $d['status']);
    }

    public function verifyWebhook(string $raw, array $h): bool
    {
        $header = $h['stripe-signature'] ?? '';
        preg_match('/(?:^|,)t=(\d+)/', $header, $tm);
        preg_match('/(?:^|,)v1=([a-f0-9]+)/i', $header, $sig);

        return isset($tm[1],$sig[1]) && abs(time() - (int) $tm[1]) <= 300 && hash_equals(hash_hmac('sha256', $tm[1].'.'.$raw, (string) $this->configuration->webhook_secret), $sig[1]);
    }

    public function normalizeWebhook(array $p): array
    {
        return ['id' => $this->string($p, 'id'), 'type' => $this->string($p, 'type', 120), 'reference' => (string) data_get($p, 'data.object.id'), 'status' => (string) data_get($p, 'data.object.status'), 'amount' => (int) (data_get($p, 'data.object.amount_received') ?? data_get($p, 'data.object.amount') ?? 0), 'currency' => strtoupper((string) data_get($p, 'data.object.currency'))];
    }
}
