<?php

namespace App\Models;

use App\Domain\Payments\RefundState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['state' => RefundState::class, 'amount_minor' => 'integer', 'completed_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
