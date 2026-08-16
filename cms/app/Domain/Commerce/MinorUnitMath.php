<?php

namespace App\Domain\Commerce;

final class MinorUnitMath
{
    public static function percentage(int $minor, int $basisPoints): int
    {
        if ($minor < 0 || $basisPoints < 0 || $basisPoints > 10000) {
            throw new \InvalidArgumentException('Invalid percentage operands.');
        }

return intdiv(($minor * $basisPoints) + 5000, 10000);
    }
}
