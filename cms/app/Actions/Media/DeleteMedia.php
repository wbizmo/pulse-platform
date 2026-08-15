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
        DB::transaction(function () use ($media, $actor): void {
            $this->audit->execute($actor, 'media.deleted', $media, ['mime_type' => $media->mime_type, 'size' => $media->size]);
            $media->delete();
        });
        if (! Storage::disk($media->disk)->delete($media->path)) {
            $media->newQuery()->create($media->getAttributes());
            throw ValidationException::withMessages(['media' => 'The stored image could not be removed; its media record was restored.']);
        }
    }
}
