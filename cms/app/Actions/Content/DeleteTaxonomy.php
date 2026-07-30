<?php

namespace App\Actions\Content;

use App\Actions\Access\RecordAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteTaxonomy
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Model $taxonomy, User $actor): void
    {
        DB::transaction(function () use ($taxonomy, $actor): void {
            if ($taxonomy->posts()->exists()) {
                throw ValidationException::withMessages(['taxonomy' => 'This taxonomy cannot be deleted while posts are assigned to it.']);
            }
            $this->audit->execute($actor, 'taxonomy.deleted', $taxonomy, ['slug' => $taxonomy->slug]);
            $taxonomy->delete();
            Cache::forget('content.sitemap');
        });
    }
}
