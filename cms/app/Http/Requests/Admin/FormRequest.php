<?php

namespace App\Http\Requests\Admin;

use App\Domain\Content\ReservedSlug;
use Illuminate\Foundation\Http\FormRequest as BaseRequest;
use Illuminate\Validation\Rule;

class FormRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('forms.manage') === true;
    }

    protected function prepareForValidation(): void
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $this->input('name')));
        $this->merge(['name' => $name, 'slug' => str($this->input('slug') ?: $name)->slug()->toString(), 'is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'alpha_dash:ascii', 'max:120', Rule::notIn(ReservedSlug::values()), Rule::unique('forms')->ignore($this->route('form'))], 'description' => ['nullable', 'string', 'max:5000'], 'success_message' => ['required', 'string', 'max:500'], 'is_active' => ['required', 'boolean']];
    }
}
