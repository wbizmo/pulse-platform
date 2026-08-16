<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfiguration extends Model
{
    protected $guarded = [];

    protected $hidden = ['secret', 'webhook_secret'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'currencies' => 'array', 'secret' => 'encrypted', 'webhook_secret' => 'encrypted'];
    }

    public function configured(): bool
    {
        return filled($this->secret) && filled($this->webhook_secret) && count($this->currencies ?? []) > 0;
    }
}
