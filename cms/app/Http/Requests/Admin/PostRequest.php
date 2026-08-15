<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafePublicUrl;

class PostRequest extends ContentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('posts.manage') ?? false;
    }

    public function rules(): array
    {
        $canAssignTaxonomy = $this->user()?->can('taxonomy.manage') ?? false;
        $seo = $this->user()?->can('seo.manage') ?? false;
        $seoRule = $seo ? 'nullable' : 'prohibited';

        return $this->lifecycleRules() + [
            'excerpt' => ['nullable', 'string', 'max:2000'], 'content' => ['nullable', 'string'],
            'featured_media_id' => [$this->user()?->can('media.manage') ? 'nullable' : 'prohibited', 'integer', 'exists:media,id'],
            'category_id' => [$canAssignTaxonomy ? 'nullable' : 'prohibited', 'integer', 'exists:categories,id'],
            'tags' => [$canAssignTaxonomy ? 'sometimes' : 'prohibited', 'array', 'max:50'], 'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
            'meta_title' => [$seoRule, 'string', 'max:70'], 'meta_description' => [$seoRule, 'string', 'max:320'],
            'meta_keywords' => [$seoRule, 'string', 'max:255'], 'canonical_url' => [$seoRule, new SafePublicUrl, 'max:255'],
            'og_title' => [$seoRule, 'string', 'max:70'], 'og_description' => [$seoRule, 'string', 'max:320'],
            'og_image' => [$seoRule, new SafePublicUrl, 'max:255'], 'twitter_title' => [$seoRule, 'string', 'max:70'],
            'twitter_description' => [$seoRule, 'string', 'max:320'], 'twitter_image' => [$seoRule, new SafePublicUrl, 'max:255'],
        ];
    }

    protected function table(): string
    {
        return 'posts';
    }

    protected function recordId(): ?int
    {
        return $this->route('post')?->getKey();
    }
}
