<?php

namespace App\Actions\Content;

use App\Actions\Access\RecordAudit;
use App\Domain\Content\ContentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveContent
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Model $content, array $data, User $actor, ?array $tagIds = null): Model
    {
        return DB::transaction(function () use ($content, $data, $actor, $tagIds): Model {
            $creating = ! $content->exists;
            $expectedVersion = (int) Arr::pull($data, 'lock_version', 0);
            $status = ContentStatus::from($data['status']);
            $data['published_at'] = match ($status) {
                ContentStatus::Published => $data['published_at'] ?? now(),
                ContentStatus::Scheduled => $data['published_at'],
                default => null,
            };

            if ($creating) {
                $content->fill($data)->save();
            } else {
                $data['lock_version'] = $expectedVersion + 1;
                $updated = $content->newQuery()->whereKey($content->getKey())->where('lock_version', $expectedVersion)->update($data);
                if ($updated !== 1) {
                    throw ValidationException::withMessages(['lock_version' => 'This content was changed by another editor. Reload it before saving again.']);
                }
                $content->refresh();
            }

            if ($tagIds !== null && method_exists($content, 'tags')) {
                $content->tags()->sync($tagIds);
            }
            $this->audit->execute($actor, $creating ? 'content.created' : 'content.updated', $content, ['status' => $status->value]);

            return $content;
        });
    }
}
