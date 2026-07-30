<?php

namespace App\Actions\Content;

use App\Actions\Access\RecordAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveTaxonomy
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Model $taxonomy, array $data, User $actor): Model
    {
        try {
            return DB::transaction(function () use ($taxonomy, $data, $actor): Model {
                $creating = ! $taxonomy->exists;
                $taxonomy->fill($data)->save();
                $this->audit->execute($actor, $creating ? 'taxonomy.created' : 'taxonomy.updated', $taxonomy, ['slug' => $taxonomy->slug]);
                Cache::forget('content.sitemap');

                return $taxonomy;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['taxonomy' => 'A taxonomy with that normalized name or slug already exists.']);
        }
    }
}
