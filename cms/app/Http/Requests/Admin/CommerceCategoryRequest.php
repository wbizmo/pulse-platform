<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommerceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('commerce.products.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug($this->input('slug') ?: $this->input('name')), 'normalized_name' => mb_strtolower(trim((string) $this->input('name')))]);
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id;

        return ['name' => ['required', 'string', 'max:120'], 'normalized_name' => ['required', 'string', 'max:120', Rule::unique('product_categories')->ignore($id)], 'slug' => ['required', 'alpha_dash:ascii', 'max:140', Rule::unique('product_categories')->ignore($id)], 'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['sometimes', 'boolean'], 'position' => ['sometimes', 'integer', 'min:0', 'max:65535']];
    }
}
