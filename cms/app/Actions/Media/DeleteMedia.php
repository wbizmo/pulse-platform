<?php

namespace App\Actions\Media;

use App\Actions\Access\RecordAudit;
use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteMedia
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Media $media, User $actor): void
    {
        $seoReference = Setting::query()->whereIn('key', ['seo_default_media_id', 'seo_organization_media_id'])->where('value', (string) $media->id)->exists();
        $builderReference = false;
        Page::query()->whereNotNull('builder_data')->select(['id', 'builder_data'])->chunkById(100, function ($pages) use (&$builderReference, $media): bool {
            if ($pages->contains(fn (Page $page) => $this->referencesBuilderMedia($page->builder_data, $media->id))) {
                $builderReference = true;

                return false;
            }

            return true;
        });
        if ($media->pages()->exists() || $media->posts()->exists() || $seoReference || $builderReference) {
            throw ValidationException::withMessages(['media' => 'This image is in use by content or SEO settings and cannot be deleted.']);
        }
        $disk = Storage::disk($media->disk);
        $contents = $disk->get($media->path);
        if (! $disk->delete($media->path)) {
            $this->audit->execute($actor, 'media.delete_failed', $media, ['reason' => 'storage_delete_failed']);
            throw ValidationException::withMessages(['media' => 'The stored image could not be removed; its media record remains available.']);
        }

        try {
            DB::transaction(function () use ($media, $actor): void {
                $media->newQuery()->whereKey($media->getKey())->lockForUpdate()->firstOrFail()->delete();
                $this->audit->execute($actor, 'media.deleted', $media, ['mime_type' => $media->mime_type, 'size' => $media->size]);
            });
        } catch (\Throwable $exception) {
            if (! $disk->put($media->path, $contents)) {
                report($exception);
                throw ValidationException::withMessages(['media' => 'The media record could not be removed and its stored image could not be restored. Operator intervention is required.']);
            }

            throw $exception;
        }
    }

    private function referencesBuilderMedia(?array $document, int $mediaId): bool
    {
        $walk = function (array $nodes) use (&$walk, $mediaId): bool {
            foreach ($nodes as $node) {
                if (($node['type'] ?? null) === 'image' && ($node['props']['media_id'] ?? null) === $mediaId) {
                    return true;
                } if (is_array($node['children'] ?? null) && $walk($node['children'])) {
                    return true;
                }
            }

            return false;
        };

        return is_array($document) && is_array($document['nodes'] ?? null) && $walk($document['nodes']);
    }
}
