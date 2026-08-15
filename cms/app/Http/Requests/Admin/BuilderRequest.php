<?php

namespace App\Http\Requests\Admin;

use App\Domain\Builder\BlockRegistry;
use Illuminate\Foundation\Http\FormRequest;

class BuilderRequest extends FormRequest
{
    public function rules(): array
    {
        return ['builder_data' => ['required', 'string', 'max:'.BlockRegistry::MAX_BYTES], 'lock_version' => ['required', 'integer', 'min:0']];
    }
}
