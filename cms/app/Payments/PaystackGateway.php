<?php

namespace App\Payments;

use App\Domain\Payments\PaymentResult;
use App\Domain\Payments\RefundResult;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Refund;

final class PaystackGateway extends AbstractHttpGateway
{
    private function request()
    {
        return $this->http()->withToken($this->configuration->secret)->baseUrl('https://api.paystack.co');
    }

    public function initiate(PaymentAttempt $a, Order $o): PaymentResult
    {
        $d = $this->request()->post('/transaction/initialize', ['email' => $o->customer_email, 'amount' => $o->total_minor, 'currency' => $o->currency, 'reference' => $a->reference, 'callback_url' => route('payments.callback', ['gateway' => 'paystack', 'reference' => $o->public_reference])])->throw()->json('data');

        return new PaymentResult($this->status('requires_action'), $this->string($d, 'reference'), 'initialized', $this->string($d, 'authorization_url', 2048));
    }

    public function verify(PaymentAttempt $a): PaymentResult
    {
        $d = $this->request()->get('/transaction/verify/'.rawurlencode((string) $a->provider_reference))->throw()->json('data');

        return new PaymentResult($this->status($this->string($d, 'status', 80)), $this->string($d, 'reference'), $d['status'], null, null, (int) $d['amount'], strtoupper((string) $d['currency']));
    }

    public function refund(Refund $r, string $p): RefundResult
    {
        $d = $this->request()->withHeader('Idempotency-Key', $r->idempotency_key)->post('/refund', ['transaction' => $p, 'amount' => $r->amount_minor, 'currency' => $r->currency, 'merchant_note' => $r->reference])->throw()->json('data');

        return new RefundResult($this->refundStatus((string) ($d['status'] ?? 'pending')), (string) ($d['id'] ?? $d['refund_reference'] ?? $r->reference), (string) ($d['status'] ?? 'pending'));
    }

    public function verifyWebhook(string $raw, array $h): bool
    {
        return isset($h['x-paystack-signature']) && hash_equals(hash_hmac('sha512', $raw, (string) $this->configuration->secret), $h['x-paystack-signature']);
    }

    public function normalizeWebhook(array $p): array
    {
        return ['id' => (string) ($p['id'] ?? hash('sha256', json_encode($p))), 'type' => $this->string($p, 'event', 120), 'reference' => (string) data_get($p, 'data.reference'), 'status' => (string) data_get($p, 'data.status'), 'amount' => (int) data_get($p, 'data.amount', 0), 'currency' => strtoupper((string) data_get($p, 'data.currency'))];
    }
}
