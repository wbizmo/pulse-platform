<?php

namespace App\Http\Requests\Admin;

class PageRequest extends ContentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.manage') ?? false;
    }

    public function rules(): array
    {
        return $this->lifecycleRules() + [
            'template' => ['required', 'string', 'in:default,landing,full-width,blog,shop,school,portfolio'],
            'content' => ['nullable', 'string'],
            'is_homepage' => ['nullable', 'boolean'], 'is_blog_page' => ['nullable', 'boolean'],
            'show_header' => ['nullable', 'boolean'], 'show_footer' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:255'], 'canonical_url' => ['nullable', 'url', 'max:2048'],
            'og_title' => ['nullable', 'string', 'max:255'], 'og_description' => ['nullable', 'string', 'max:1000'],
            'og_image' => ['nullable', 'url', 'max:2048'], 'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:1000'], 'twitter_image' => ['nullable', 'url', 'max:2048'],
        ];
    }

    protected function table(): string
    {
        return 'pages';
    }

    protected function recordId(): ?int
    {
        return $this->route('page')?->getKey();
    }
}
