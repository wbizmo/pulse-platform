<?php

namespace App\Actions\Access;

use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EnsureSuperAdministratorRemains
{
    public function execute(User $target, bool $willBeActive, array $newRoleIds): void
    {
        if (! $target->isSuperAdministrator() || ($willBeActive && Role::whereIn('id', $newRoleIds)->where('is_super_admin', true)->exists())) {
            return;
        }
        $other = User::query()->whereKeyNot($target->id)->where('status', 'active')->whereHas('roles', fn ($q) => $q->where('is_super_admin', true))->lockForUpdate()->exists();
        if (! $other) {
            throw ValidationException::withMessages(['roles' => ['The final active super administrator cannot be disabled, demoted, or deleted.']]);
        }
    }
}
