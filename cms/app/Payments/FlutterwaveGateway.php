<?php

namespace App\Payments;

use App\Domain\Payments\PaymentResult;
use App\Domain\Payments\RefundResult;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Refund;

final class FlutterwaveGateway extends AbstractHttpGateway
{
    private function request()
    {
        return $this->http()->withToken($this->configuration->secret)->baseUrl('https://api.flutterwave.com/v3');
    }

    public function initiate(PaymentAttempt $a, Order $o): PaymentResult
    {
        $d = $this->request()->withHeader('X-Idempotency-Key', $a->idempotency_key)->post('/payments', ['tx_ref' => $a->reference, 'amount' => (string) $o->total_minor, 'currency' => $o->currency, 'redirect_url' => route('payments.callback', ['gateway' => 'flutterwave', 'reference' => $o->public_reference]), 'customer' => ['email' => $o->customer_email, 'name' => $o->shipping_address['full_name']]])->throw()->json('data');

        return new PaymentResult($this->status('requires_action'), $a->reference, 'initialized', $this->string($d, 'link', 2048));
    }

    public function verify(PaymentAttempt $a): PaymentResult
    {
        $d = $this->request()->get('/transactions/verify_by_reference', ['tx_ref' => $a->reference])->throw()->json('data');

        return new PaymentResult($this->status($this->string($d, 'status', 80)), (string) ($d['id'] ?? $a->provider_reference), $d['status'], null, null, (int) $d['amount'], strtoupper((string) $d['currency']));
    }

    public function refund(Refund $r, string $p): RefundResult
    {
        $d = $this->request()->withHeader('X-Idempotency-Key', $r->idempotency_key)->post('/transactions/'.rawurlencode($p).'/refund', ['amount' => $r->amount_minor, 'comments' => $r->reference])->throw()->json('data');

        return new RefundResult($this->refundStatus((string) ($d['status'] ?? 'pending')), (string) ($d['id'] ?? $r->reference), (string) ($d['status'] ?? 'pending'));
    }

    public function verifyWebhook(string $raw, array $h): bool
    {
        $signature = $h['flutterwave-signature'] ?? '';
        $expected = base64_encode(hash_hmac('sha256', $raw, (string) $this->configuration->webhook_secret, true));

        return $signature !== '' && hash_equals($expected, $signature);
    }

    public function normalizeWebhook(array $p): array
    {
        return ['id' => (string) ($p['id'] ?? data_get($p, 'data.id') ?? hash('sha256', json_encode($p))), 'type' => (string) ($p['type'] ?? $p['event'] ?? 'unknown'), 'reference' => (string) data_get($p, 'data.tx_ref'), 'status' => (string) data_get($p, 'data.status'), 'amount' => (int) data_get($p, 'data.amount', 0), 'currency' => strtoupper((string) data_get($p, 'data.currency'))];
    }
}
