<?php

namespace App\Domain\Commerce;

enum OrderState: string
{
    case AwaitingPayment = 'awaiting_payment';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function canTransitionTo(self $next): bool
    {
        return $this === self::AwaitingPayment && in_array($next, [self::Cancelled, self::Expired], true);
    }
}
