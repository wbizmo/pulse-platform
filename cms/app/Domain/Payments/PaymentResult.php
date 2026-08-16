<?php

namespace App\Domain\Payments;

final readonly class PaymentResult
{
    public function __construct(public PaymentState $state, public string $providerReference, public ?string $providerStatus = null, public ?string $redirectUrl = null, public ?string $clientSecret = null, public ?int $amountMinor = null, public ?string $currency = null) {}
}
