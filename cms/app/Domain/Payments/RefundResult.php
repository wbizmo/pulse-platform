<?php

namespace App\Domain\Payments;

final readonly class RefundResult
{
    public function __construct(public RefundState $state, public string $providerReference, public ?string $providerStatus = null) {}
}
