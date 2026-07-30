<?php

namespace App\Http\Requests\Admin;

use App\Domain\Access\Permission;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageRoles->value) ?? false;
    }

    public function rules(): array
    {
        return ['label' => ['required', 'string', 'max:100'], 'name' => ['required', 'alpha_dash:ascii', 'max:100', 'unique:roles,name'], 'permissions' => ['array'], 'permissions.*' => ['string', 'distinct', 'exists:permissions,name']];
    }
}
