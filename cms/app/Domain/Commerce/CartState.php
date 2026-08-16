<?php

namespace App\Domain\Commerce;

enum CartState: string
{
    case Active = 'active';
    case Converted = 'converted';
    case Expired = 'expired';
}
