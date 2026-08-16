<?php

namespace App\Payments;

use App\Domain\Payments\PaymentResult;
use App\Domain\Payments\PaymentState;
use App\Domain\Payments\RefundResult;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Refund;
use Illuminate\Support\Facades\Http;

final class PayPalGateway extends AbstractHttpGateway
{
    private function base(): string
    {
        return $this->configuration->environment === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function token(): string
    {
        return Http::asForm()->withBasicAuth((string) $this->configuration->public_identifier, (string) $this->configuration->secret)->connectTimeout(5)->timeout(15)->post($this->base().'/v1/oauth2/token', ['grant_type' => 'client_credentials'])->throw()->json('access_token');
    }

    private function request()
    {
        return $this->http()->withToken($this->token())->baseUrl($this->base());
    }

    private function decimal(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    private function minor(string $amount): int
    {
        if (! preg_match('/^\d+(?:\.(\d{1,2}))?$/', $amount, $m)) {
            return 0;
        } [$whole,$fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    public function initiate(PaymentAttempt $a, Order $o): PaymentResult
    {
        $d = $this->request()->withHeader('PayPal-Request-Id', $a->idempotency_key)->post('/v2/checkout/orders', ['intent' => 'CAPTURE', 'purchase_units' => [['reference_id' => $a->reference, 'custom_id' => $o->public_reference, 'amount' => ['currency_code' => $o->currency, 'value' => $this->decimal($o->total_minor)]]], 'payment_source' => ['paypal' => ['experience_context' => ['return_url' => route('payments.callback', ['gateway' => 'paypal', 'reference' => $o->public_reference]), 'cancel_url' => route('orders.show', $o->public_reference)]]]])->throw()->json();
        $url = collect($d['links'] ?? [])->firstWhere('rel', 'payer-action')['href'] ?? collect($d['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        return new PaymentResult(PaymentState::RequiresAction, $this->string($d, 'id'), (string) $d['status'], $url);
    }

    public function verify(PaymentAttempt $a): PaymentResult
    {
        $d = $this->request()->get('/v2/checkout/orders/'.rawurlencode((string) $a->provider_reference))->throw()->json();
        $capture = data_get($d, 'purchase_units.0.payments.captures.0', []);

        return new PaymentResult($this->status((string) ($capture['status'] ?? $d['status'])), $this->string($d, 'id'), (string) ($capture['status'] ?? $d['status']), null, null, $this->minor((string) data_get($capture, 'amount.value', '0')), strtoupper((string) data_get($capture, 'amount.currency_code')));
    }

    public function refund(Refund $r, string $p): RefundResult
    {
        $d = $this->request()->withHeader('PayPal-Request-Id', $r->idempotency_key)->post('/v2/payments/captures/'.rawurlencode($p).'/refund', ['amount' => ['value' => $this->decimal($r->amount_minor), 'currency_code' => $r->currency], 'invoice_id' => $r->reference])->throw()->json();

        return new RefundResult($this->refundStatus((string) $d['status']), $this->string($d, 'id'), $d['status']);
    }

    public function verifyWebhook(string $raw, array $h): bool
    {
        $event = json_decode($raw, true);
        if (! is_array($event) || ! filled($this->configuration->webhook_secret)) {
            return false;
        }$d = $this->request()->post('/v1/notifications/verify-webhook-signature', ['auth_algo' => $h['paypal-auth-algo'] ?? null, 'cert_url' => $h['paypal-cert-url'] ?? null, 'transmission_id' => $h['paypal-transmission-id'] ?? null, 'transmission_sig' => $h['paypal-transmission-sig'] ?? null, 'transmission_time' => $h['paypal-transmission-time'] ?? null, 'webhook_id' => $this->configuration->webhook_secret, 'webhook_event' => $event])->throw()->json();

        return ($d['verification_status'] ?? null) === 'SUCCESS';
    }

    public function normalizeWebhook(array $p): array
    {
        return ['id' => $this->string($p, 'id'), 'type' => $this->string($p, 'event_type', 120), 'reference' => (string) (data_get($p, 'resource.supplementary_data.related_ids.order_id') ?? data_get($p, 'resource.id')), 'status' => (string) data_get($p,'resource.status'), 'amount' => $this->minor((string) data_get($p,'resource.amount.value','0')), 'currency' => strtoupper((string) data_get($p,'resource.amount.currency_code'))];
    }
}
