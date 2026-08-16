<?php

namespace App\Domain\Commerce;

enum OrderState: string
{
    case AwaitingPayment = 'awaiting_payment';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Paid = 'paid';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::AwaitingPayment => in_array($next, [self::Cancelled, self::Expired, self::Paid], true),
            self::Paid => in_array($next, [self::PartiallyRefunded, self::Refunded], true),
            self::PartiallyRefunded => $next === self::Refunded,
            default => false,
        };
    }
}
