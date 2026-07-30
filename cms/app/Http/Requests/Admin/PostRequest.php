<?php

namespace App\Http\Requests\Admin;

class PostRequest extends ContentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('posts.manage') ?? false;
    }

    public function rules(): array
    {
        return $this->lifecycleRules() + [
            'excerpt' => ['nullable', 'string', 'max:2000'], 'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'url', 'max:2048'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tags' => ['array', 'max:50'], 'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
            'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:1000'],
            'og_image' => ['nullable', 'url', 'max:2048'],
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
