<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') === true;
    }

    public function rules(): array
    {
        return ['items' => ['required', 'array', 'max:200'], 'items.*' => ['required', 'integer', 'distinct']];
    }
}
