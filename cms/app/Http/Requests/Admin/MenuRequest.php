<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') === true;
    }

    protected function prepareForValidation(): void
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $this->input('name')));
        $this->merge(['name' => $name, 'slug' => str($this->input('slug') ?: $name)->slug()->toString(), 'is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('menus')->ignore($this->route('menu'))],
            'location' => ['required', Rule::in(['main', 'footer', 'legal', 'sidebar', 'custom'])],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
