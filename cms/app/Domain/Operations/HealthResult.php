<?php

namespace App\Domain\Operations;

final readonly class HealthResult
{
    public function __construct(public string $key, public string $label, public HealthStatus $status, public string $summary, public \DateTimeImmutable $checkedAt, public array $metadata = []) {}
}
