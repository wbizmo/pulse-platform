<?php

namespace App\Models;

use App\Domain\Payments\PaymentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['state' => PaymentState::class, 'amount_minor' => 'integer', 'captured_minor' => 'integer', 'refunded_minor' => 'integer', 'paid_at' => 'datetime', 'reconciliation_required_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(PaymentDispute::class);
    }
}
