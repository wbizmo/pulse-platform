<?php

namespace App\Domain\Commerce;

use InvalidArgumentException;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case NGN = 'NGN';
    case CAD = 'CAD';
    case AUD = 'AUD';
    case JPY = 'JPY';
    case ZAR = 'ZAR';
    case KES = 'KES';
    case GHS = 'GHS';

    public static function parse(string $value): self
    {
        return self::tryFrom(strtoupper($value)) ?? throw new InvalidArgumentException('Unsupported currency.');
    }
}
