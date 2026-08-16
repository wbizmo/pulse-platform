<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStateHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'from_state', 'to_state', 'reason', 'actor_id', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
