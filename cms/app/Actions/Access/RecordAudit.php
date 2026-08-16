<?php

namespace App\Actions\Access;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Operations\Redactor;
use Illuminate\Database\Eloquent\Model;

class RecordAudit
{
    public function __construct(private readonly Redactor $redactor) {}

    public function execute(User $actor, string $action, Model $target, array $context = []): void
    {
        AuditLog::create(['actor_id' => $actor->id, 'action' => $action, 'target_type' => $target->getMorphClass(), 'target_id' => $target->getKey(), 'context' => $context ? $this->redactor->redact($context) : null, 'ip_address' => request()->ip()]);
    }
}
