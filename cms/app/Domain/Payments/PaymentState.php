<?php

namespace App\Domain\Payments;

enum PaymentState: string
{
    case Initialized = 'initialized';
    case RequiresAction = 'requires_action';
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        if ($this === $next) {
            return true;
        } if ($this === self::Succeeded) {
            return false;
        }

        return match ($this) {
            self::Initialized => in_array($next, [self::RequiresAction, self::Pending, self::Succeeded, self::Failed, self::Cancelled], true),self::RequiresAction,self::Pending => in_array($next, [self::Pending, self::Succeeded, self::Failed, self::Cancelled], true),self::Failed,self::Cancelled => false,self::Succeeded => false
        };
    }
}
