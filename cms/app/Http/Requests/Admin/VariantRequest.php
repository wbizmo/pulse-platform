<?php

namespace App\Http\Requests\Admin;

use App\Domain\Commerce\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('commerce.products.manage') ?? false;
    }

    public function rules(): array
    {
        return ['sku' => ['required', 'string', 'min:2', 'max:64', 'regex:/\A[A-Za-z0-9][A-Za-z0-9._-]+\z/'], 'price_minor' => ['required', 'integer', 'min:0', 'max:9000000000000000'], 'currency' => ['required', Rule::enum(Currency::class)], 'is_active' => ['required', 'boolean'], 'tracks_stock' => ['required', 'boolean'], 'options' => ['sometimes', 'array', 'max:10'], 'options.*' => ['required', 'string', 'max:80']];
    }
}
