<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    protected $fillable = ['country_code', 'region', 'rate_basis_points', 'priority', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'rate_basis_points' => 'integer', 'priority' => 'integer'];
    }
}
