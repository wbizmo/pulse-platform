<?php

namespace App\Http\Requests\Admin;

use App\Domain\Builder\BlockRegistry;
use Illuminate\Foundation\Http\FormRequest;

class BuilderTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'builder_data' => ['required', 'string', 'max:'.BlockRegistry::MAX_BYTES]];
    }
}
