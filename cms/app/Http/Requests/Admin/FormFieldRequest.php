<?php

namespace App\Http\Requests\Admin;

use App\Domain\Forms\FieldSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('forms.manage') === true;
    }

    protected function prepareForValidation(): void
    {
        $config = $this->input('configuration', []);
        if (is_string($config)) {
            $config = json_decode($config, true);
        } $this->merge(['key' => str((string) $this->input('key'))->snake()->toString(), 'required' => $this->boolean('required'), 'configuration' => $config]);
    }

    public function rules(): array
    {
        $form = $this->route('form');

        return ['key' => ['required', 'regex:/^[a-z][a-z0-9_]{0,63}$/', Rule::unique('form_fields')->where('form_id', $form->id)->ignore($this->route('field'))], 'label' => ['required', 'string', 'max:120'], 'type' => ['required', Rule::in(FieldSchema::TYPES)], 'help' => ['nullable', 'string', 'max:500'], 'placeholder' => ['nullable', 'string', 'max:200'], 'required' => ['required', 'boolean'], 'configuration' => ['array']];
    }

    protected function passedValidation(): void
    {
        $this->merge(['configuration' => FieldSchema::normalize($this->string('type')->toString(), $this->input('configuration', []))]);
    }
}
