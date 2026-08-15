<?php

namespace App\Http\Requests\Admin;

use App\Domain\Access\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageMedia->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', File::types(['jpg', 'jpeg', 'png', 'webp', 'gif'])->max(config('media.max_kilobytes')), 'dimensions:max_width=10000,max_height=10000'],
        ];
    }
}
