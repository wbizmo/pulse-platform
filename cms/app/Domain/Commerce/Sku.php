<?php

namespace App\Domain\Commerce;

use InvalidArgumentException;

final readonly class Sku
{
    public string $normalized;

    public function __construct(public string $value)
    {
        $this->normalized = mb_strtoupper(trim($value), 'UTF-8');
        if (! preg_match('/\A[A-Z0-9][A-Z0-9._-]{1,63}\z/u', $this->normalized)) {
            throw new InvalidArgumentException('SKU must contain 2–64 letters, numbers, dots, dashes, or underscores.');
        }
    }
}
