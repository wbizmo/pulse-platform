<?php

namespace App\Http\Requests\Admin;

use App\Domain\Commerce\ProductState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('commerce.products.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug($this->input('slug') ?: $this->input('name'))]);
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return ['name' => ['required', 'string', 'max:180'], 'slug' => ['required', 'alpha_dash:ascii', 'max:200', Rule::unique('products')->ignore($id)], 'short_description' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string', 'max:10000'], 'state' => ['required', Rule::enum(ProductState::class)], 'featured_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where(fn ($q) => $q->where('type', 'image'))], 'category_ids' => ['array', 'max:20'], 'category_ids.*' => ['integer', 'distinct', 'exists:product_categories,id'], 'gallery_media_ids' => ['array', 'max:20'], 'gallery_media_ids.*' => ['integer', 'distinct', Rule::exists('media', 'id')->where(fn ($q) => $q->where('type', 'image'))]];
    }
}
