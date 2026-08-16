<?php

namespace App\Domain\Commerce;

enum ProductState: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
