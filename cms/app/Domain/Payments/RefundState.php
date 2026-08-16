<?php

namespace App\Domain\Payments;

enum RefundState: string
{
    case Requested = 'requested';
    case Pending = 'pending';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        if ($this === $next) {
            return true;
        } if (in_array($this, [self::Succeeded, self::Failed, self::Cancelled], true)) {
            return false;
        }

return true;
    }
}
