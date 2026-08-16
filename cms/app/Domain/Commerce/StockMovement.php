<?php

namespace App\Domain\Commerce;

enum StockMovement: string
{
    case Opening = 'opening';
    case Receipt = 'receipt';
    case AdjustmentIncrease = 'adjustment_increase';
    case AdjustmentDecrease = 'adjustment_decrease';
    case Reservation = 'reservation';
    case ReservationRelease = 'reservation_release';
    case ReservationExpiry = 'reservation_expiry';
    case ReservationConsume = 'reservation_consume';
}
