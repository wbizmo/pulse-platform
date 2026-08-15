<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderFormFieldsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('forms.manage') === true;
    }

    public function rules(): array
    {
        return ['fields' => ['required', 'array', 'min:1', 'max:50'], 'fields.*' => ['integer', 'distinct']];
    }
}
