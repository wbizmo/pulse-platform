<?php

namespace App\Http\Requests\Admin;

use App\Domain\Content\MenuLink;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['page', 'custom'])],
            'page_id' => [Rule::requiredIf(fn () => $this->input('type') === 'page'), Rule::prohibitedIf(fn () => $this->input('type') === 'custom'), 'nullable', 'integer', 'exists:pages,id'],
            'url' => [Rule::requiredIf(fn () => $this->input('type') === 'custom'), Rule::prohibitedIf(fn () => $this->input('type') === 'page'), 'nullable', 'string', 'max:2048', function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && ! MenuLink::isSafe($value)) {
                    $fail('The URL must be a safe root-relative, HTTP, or HTTPS link.');
                }
            }],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
