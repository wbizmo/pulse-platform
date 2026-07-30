<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'action', 'target_type', 'target_id', 'context', 'ip_address'];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
