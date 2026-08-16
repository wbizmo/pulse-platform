<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalState extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['last_started_at' => 'datetime', 'last_completed_at' => 'datetime', 'metadata' => 'array'];
    }
}
