<?php

namespace App\Services\Builder;

use App\Domain\Builder\BuilderDocument;
use App\Models\Media;
use App\Models\Page;

final class BuilderRenderer
{
    public function __construct(private readonly BuilderDocument $documents) {}

    public function forPage(Page $page): ?array
    {
        if (! $page->builder_data) {
            return null;
        }
        try {
            $document = $this->documents->decode(json_encode($page->builder_data, JSON_THROW_ON_ERROR));
        } catch (\Throwable) {
            return null;
        }
        $media = Media::query()->whereKey($this->documents->mediaIds($document))->get()->keyBy('id');

        return ['document' => $document, 'media' => $media];
    }
}
