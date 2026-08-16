<?php

namespace App\Domain\Commerce;

enum ReservationState: string
{
    case Active = 'active';
    case Released = 'released';
    case Expired = 'expired';
    case Consumed = 'consumed';
}
