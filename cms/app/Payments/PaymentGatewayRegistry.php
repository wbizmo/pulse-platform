<?php

namespace App\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Models\PaymentGatewayConfiguration;
use Illuminate\Validation\ValidationException;

final class PaymentGatewayRegistry
{
    private const MAP = ['stripe' => StripeGateway::class, 'paypal' => PayPalGateway::class, 'flutterwave' => FlutterwaveGateway::class, 'paystack' => PaystackGateway::class];

    public function slugs(): array
    {
        return array_keys(self::MAP);
    }

    public function resolve(string $slug): PaymentGateway
    {
        if (! isset(self::MAP[$slug])) {
            throw ValidationException::withMessages(['gateway' => 'Unknown payment gateway.']);
        }$config = PaymentGatewayConfiguration::where('gateway', $slug)->first();
        if (! $config) {
            throw ValidationException::withMessages(['gateway' => 'Payment gateway is not configured.']);
        }

return new (self::MAP[$slug])($config);
    }

    public function available(string $currency): array
    {
        return PaymentGatewayConfiguration::query()->whereIn('gateway', $this->slugs())->where('enabled', true)->get()->filter(fn ($c) => $c->configured() && in_array(strtoupper($currency), array_map('strtoupper', $c->currencies ?? []), true))->map(fn ($c) => ['slug' => $c->gateway, 'name' => ucfirst($c->gateway), 'flow' => $c->gateway === 'stripe' ? 'embedded' : 'redirect'])->values()->all();
    }
}
