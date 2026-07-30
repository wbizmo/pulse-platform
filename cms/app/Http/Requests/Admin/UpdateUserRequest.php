<?php

namespace App\Http\Requests\Admin;

use App\Domain\Access\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageUsers->value) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))], 'password' => ['nullable', 'confirmed', Password::defaults()], 'status' => ['required', 'in:active,inactive'], 'roles' => ['required', 'array', 'min:1'], 'roles.*' => ['integer', 'distinct', 'exists:roles,id']];
    }
}
