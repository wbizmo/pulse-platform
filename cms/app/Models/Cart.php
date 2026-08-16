<?php

namespace App\Models;

use App\Domain\Commerce\CartState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['token_hash', 'currency', 'state', 'version', 'expires_at'];

    protected function casts(): array
    {
        return ['state' => CartState::class, 'expires_at' => 'datetime', 'version' => 'integer'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
