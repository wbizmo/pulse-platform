<?php

namespace App\Models;

use App\Domain\Payments\PaymentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['state' => PaymentState::class, 'action' => 'array', 'initiated_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
