<?php

namespace App\Actions\Builder;

use App\Actions\Access\RecordAudit;
use App\Domain\Access\Permission;
use App\Domain\Builder\BuilderDocument;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class SaveBuilder
{
    public function __construct(private readonly BuilderDocument $documents, private readonly RecordAudit $audit) {}

    public function execute(Page $page, User $actor, array $document, int $loadedVersion): Page
    {
        $existingMedia = $page->builder_data ? $this->documents->mediaIds($page->builder_data) : [];
        $selectedMedia = $this->documents->mediaIds($document);
        if (array_diff($selectedMedia, $existingMedia) && Gate::forUser($actor)->denies(Permission::ManageMedia->value)) {
            throw ValidationException::withMessages(['builder_data' => 'Selecting managed images requires media authority.']);
        }

        $saved = DB::transaction(function () use ($page, $actor, $document, $loadedVersion): Page {
            $updated = Page::query()->whereKey($page->id)->where('lock_version', $loadedVersion)->update([
                'builder_data' => $document,
                'lock_version' => DB::raw('lock_version + 1'),
                'updated_at' => now(),
            ]);
            if ($updated !== 1) {
                throw ValidationException::withMessages(['lock_version' => 'This page changed after you opened Builder. Reload and reconcile your draft before saving.']);
            }
            $fresh = Page::query()->findOrFail($page->id);
            $this->audit->execute($actor, 'builder.saved', $fresh, ['schema_version' => $document['schema_version'], 'node_count' => $this->documents->nodeCount($document), 'from_version' => $loadedVersion, 'to_version' => $fresh->lock_version]);

            return $fresh;
        });
        Cache::forget('sitemap.xml');

        return $saved;
    }
}
