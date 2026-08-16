<?php

namespace App\Models;

use App\Services\Operations\Redactor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    protected static function booted(): void
    {
        static::creating(function (AuditLog $audit): void {
            if (is_array($audit->context)) {
                $audit->context = app(Redactor::class)->redact($audit->context);
            }
        });
        static::updating(fn () => throw new LogicException('Audit records are append-only.'));
        static::deleting(fn () => throw new LogicException('Audit records are append-only.'));
    }

    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'action', 'target_type', 'target_id', 'context', 'ip_address'];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
