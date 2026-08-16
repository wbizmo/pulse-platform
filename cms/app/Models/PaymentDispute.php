<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDispute extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer', 'respond_by' => 'datetime', 'opened_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
