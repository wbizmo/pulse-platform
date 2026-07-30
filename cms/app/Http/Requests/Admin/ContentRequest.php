<?php

namespace App\Http\Requests\Admin;

use App\Domain\Content\ContentStatus;
use App\Domain\Content\ReservedSlug;
use App\Rules\AvailableContentSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class ContentRequest extends FormRequest
{
    protected function lifecycleRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', new AvailableContentSlug($this->table(), $this->recordId())],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            'lock_version' => ['required', 'integer', 'min:0'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['slug' => ReservedSlug::normalize((string) ($this->input('slug') ?: $this->input('title')))]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['status', 'published_at'])) {
                return;
            }
            $status = ContentStatus::tryFrom((string) $this->input('status'));
            $publication = $this->date('published_at');
            if ($status === ContentStatus::Scheduled && (! $publication || ! $publication->isFuture())) {
                $validator->errors()->add('published_at', 'Scheduled content requires a future publication time.');
            }
            if ($status === ContentStatus::Published && $publication && $publication->isFuture()) {
                $validator->errors()->add('published_at', 'Published content cannot have a future publication time.');
            }
            if (in_array($status, [ContentStatus::Draft, ContentStatus::Archived], true) && $publication) {
                $validator->errors()->add('published_at', 'Draft and archived content cannot retain a publication time.');
            }
        });
    }

    abstract protected function table(): string;

    abstract protected function recordId(): ?int;
}
