<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafePublicUrl;

class PageRequest extends ContentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.manage') ?? false;
    }

    public function rules(): array
    {
        $seo = $this->user()?->can('seo.manage') ?? false;
        $seoRule = $seo ? 'nullable' : 'prohibited';

        return $this->lifecycleRules() + [
            'template' => ['required', 'string', 'in:default,landing,full-width,blog,shop,school,portfolio'],
            'content' => ['nullable', 'string'],
            'featured_media_id' => [$this->user()?->can('media.manage') ? 'nullable' : 'prohibited', 'integer', 'exists:media,id'],
            'is_homepage' => ['nullable', 'boolean'], 'is_blog_page' => ['nullable', 'boolean'],
            'show_header' => ['nullable', 'boolean'], 'show_footer' => ['nullable', 'boolean'],
            'meta_title' => [$seoRule, 'string', 'max:70'], 'meta_description' => [$seoRule, 'string', 'max:320'],
            'meta_keywords' => [$seoRule, 'string', 'max:255'], 'canonical_url' => [$seoRule, new SafePublicUrl, 'max:255'],
            'og_title' => [$seoRule, 'string', 'max:70'], 'og_description' => [$seoRule, 'string', 'max:320'],
            'og_image' => [$seoRule, new SafePublicUrl, 'max:255'], 'twitter_title' => [$seoRule, 'string', 'max:70'],
            'twitter_description' => [$seoRule, 'string', 'max:320'], 'twitter_image' => [$seoRule, new SafePublicUrl, 'max:255'],
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
