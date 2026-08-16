<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $a = $this->input('shipping_address');
        if (is_array($a) && isset($a['country_code'])) {
            $a['country_code'] = mb_strtoupper(trim((string) $a['country_code']));
            $this->merge(['shipping_address' => $a]);
        }
    }

    public function rules(): array
    {
        $countries = ['AU', 'BR', 'CA', 'CN', 'DE', 'ES', 'FR', 'GB', 'GH', 'IN', 'IT', 'JP', 'KE', 'MX', 'NG', 'NL', 'NZ', 'SG', 'US', 'ZA'];

        return ['idempotency_key' => ['required', 'string', 'min:32', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'], 'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'], 'coupon_code' => ['nullable', 'string', 'max:80'], 'shipping_address' => ['required', 'array:full_name,organization,line1,line2,city,region,postal_code,country_code,email,phone'], 'shipping_address.full_name' => ['required', 'string', 'max:160'], 'shipping_address.organization' => ['nullable', 'string', 'max:160'], 'shipping_address.line1' => ['required', 'string', 'max:200'], 'shipping_address.line2' => ['nullable', 'string', 'max:200'], 'shipping_address.city' => ['required', 'string', 'max:120'], 'shipping_address.region' => ['nullable', 'string', 'max:120'], 'shipping_address.postal_code' => ['nullable', 'string', 'max:40'], 'shipping_address.country_code' => ['required', Rule::in($countries)], 'shipping_address.email' => ['required', 'email:rfc', 'max:254'], 'shipping_address.phone' => ['nullable', 'string', 'max:40']];
    }
}
