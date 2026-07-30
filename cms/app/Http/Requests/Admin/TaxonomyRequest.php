<?php

namespace App\Http\Requests\Admin;

use App\Domain\Content\Taxonomy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxonomyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('taxonomy.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $this->input('name'))) ?? '';
        $this->merge([
            'name' => $name,
            'normalized_name' => Taxonomy::normalizeName($name),
            'slug' => Taxonomy::normalizeSlug($this->input('slug'), $name),
        ]);
    }

    public function rules(): array
    {
        $type = $this->routeIs('admin.categories.*') ? 'category' : 'tag';
        $table = $type === 'category' ? 'categories' : 'tags';
        $record = $this->route($type);

        return [
            'name' => ['required', 'string', 'min:1', 'max:'.Taxonomy::MAX_NAME_LENGTH],
            'normalized_name' => ['required', Rule::unique($table)->ignore($record)],
            'slug' => ['required', 'alpha_dash:ascii', 'max:'.Taxonomy::MAX_SLUG_LENGTH, Rule::notIn(Taxonomy::RESERVED_SLUGS), Rule::unique($table)->ignore($record)],
            'description' => [$type === 'category' ? 'nullable' : 'prohibited', 'string', 'max:2000'],
        ];
    }
}
