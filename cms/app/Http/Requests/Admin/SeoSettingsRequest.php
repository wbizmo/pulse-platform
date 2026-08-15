<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeoSettingsRequest extends FormRequest
{
    public const TEXT_KEYS = ['seo_default_title', 'seo_default_description', 'seo_default_keywords', 'seo_robots_content', 'seo_organization_name'];

    public const BOOLEAN_KEYS = ['seo_sitemap_enabled', 'seo_robots_enabled', 'seo_canonical_enabled', 'seo_noindex_enabled', 'seo_schema_enabled', 'seo_social_enabled'];

    public function authorize(): bool
    {
        return $this->user()?->can('seo.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'seo_default_title' => ['nullable', 'string', 'max:70'],
            'seo_default_description' => ['nullable', 'string', 'max:320'],
            'seo_default_keywords' => ['nullable', 'string', 'max:255'],
            'seo_default_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'seo_robots_content' => ['nullable', 'string', 'max:10000', 'not_regex:/[\x00\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/'],
            'seo_schema_type' => ['required', 'string', Rule::in(['WebSite', 'Organization', 'LocalBusiness', 'Person'])],
            'seo_organization_name' => ['nullable', 'string', 'max:255'],
            'seo_organization_media_id' => ['nullable', 'integer', 'exists:media,id'],
            ...array_fill_keys(self::BOOLEAN_KEYS, ['boolean']),
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];
        foreach (self::TEXT_KEYS as $key) {
            if (is_string($this->input($key))) {
                $normalized[$key] = trim(str_replace(["\r\n", "\r"], "\n", $this->input($key))) ?: null;
            }
        }
        foreach (self::BOOLEAN_KEYS as $key) {
            $normalized[$key] = $this->boolean($key);
        }
        $this->merge($normalized);
    }
}
