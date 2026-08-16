<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class CartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['variant_id' => ['required', 'integer', 'exists:product_variants,id'], 'quantity' => ['required', 'integer', 'between:1,100']];
    }
}
