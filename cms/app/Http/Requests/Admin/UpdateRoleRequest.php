<?php

namespace App\Http\Requests\Admin;

use App\Domain\Access\Permission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can(Permission::ManageRoles->value) ?? false) && ! $this->route('role')->is_system;
    }

    public function rules(): array
    {
        return ['label' => ['required', 'string', 'max:100'], 'permissions' => ['array'], 'permissions.*' => ['string', 'distinct', 'exists:permissions,name']];
    }
}
