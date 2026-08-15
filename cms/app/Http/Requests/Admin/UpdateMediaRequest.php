<?php

namespace App\Http\Requests\Admin;

use App\Domain\Access\Permission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageMedia->value) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'alt_text' => ['nullable', 'string', 'max:500'], 'caption' => ['nullable', 'string', 'max:2000']];
    }
}
