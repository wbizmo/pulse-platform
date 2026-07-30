<?php

namespace App\Actions\Access;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RecordAudit
{
    public function execute(User $actor, string $action, Model $target, array $context = []): void
    {
        AuditLog::create(['actor_id' => $actor->id, 'action' => $action, 'target_type' => $target->getMorphClass(), 'target_id' => $target->getKey(), 'context' => $context ?: null, 'ip_address' => request()->ip()]);
    }
}
