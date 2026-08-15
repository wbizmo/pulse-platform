<?php

namespace App\Actions\Media;

use App\Actions\Access\RecordAudit;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteMedia
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Media $media, User $actor): void
    {
        if ($media->pages()->exists() || $media->posts()->exists()) {
            throw ValidationException::withMessages(['media' => 'This image is in use by page or post content and cannot be deleted.']);
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
}
