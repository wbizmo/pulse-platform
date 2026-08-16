<?php

namespace App\Domain\Commerce;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(public int $amountMinor, public Currency $currency)
    {
        if ($amountMinor < 0 || $amountMinor > 9_000_000_000_000_000) {
            throw new InvalidArgumentException('Money amount is outside the supported range.');
        }
    }

    public function format(string $locale = 'en'): string
    {
        $digits = $this->currency === Currency::JPY ? 0 : 2;
        if ($digits === 0) {
            return $this->currency->value.' '.number_format($this->amountMinor, 0, '.', ',');
        }
        $major = intdiv($this->amountMinor, 100);
        $minor = $this->amountMinor % 100;

        return $this->currency->value.' '.number_format($major, 0, '.', ',').'.'.str_pad((string) $minor, 2, '0', STR_PAD_LEFT);
    }
}
