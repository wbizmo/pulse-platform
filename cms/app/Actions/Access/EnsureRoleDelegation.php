<?php

namespace App\Actions\Access;

use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EnsureRoleDelegation
{
    public function execute(User $actor, array $roleIds): array
    {
        $roles = Role::with('permissions')->whereIn('id', $roleIds)->get();
        if ($roles->count() !== count(array_unique($roleIds))) {
            throw ValidationException::withMessages(['roles' => ['One or more selected roles are invalid.']]);
        }
        $allowed = $actor->permissionNames();
        foreach ($roles as $role) {
            if ($role->is_super_admin && ! $actor->isSuperAdministrator()) {
                throw ValidationException::withMessages(['roles' => ['You cannot delegate super-administrator access.']]);
            }
            if (array_diff($role->permissions->pluck('name')->all(), $allowed)) {
                throw ValidationException::withMessages(['roles' => ['You cannot delegate permissions you do not hold.']]);
            }
        }

        return $roles->modelKeys();
    }
}
