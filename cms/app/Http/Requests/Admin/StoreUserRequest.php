<?php

namespace App\Http\Requests\Admin;

use App\Domain\Access\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageUsers->value) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'confirmed', Password::defaults()], 'status' => ['required', 'in:active,inactive'], 'roles' => ['required', 'array', 'min:1'], 'roles.*' => ['integer', 'distinct', 'exists:roles,id']];
    }
}
