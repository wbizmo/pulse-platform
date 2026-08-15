<?php

namespace App\Actions\Content;

use App\Actions\Access\RecordAudit;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderMenuItems
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Menu $menu, array $ids, User $actor): void
    {
        DB::transaction(function () use ($menu, $ids, $actor): void {
            $actual = $menu->items()->lockForUpdate()->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
            if (count($ids) !== count($actual) || array_diff($ids, $actual) || array_diff($actual, $ids)) {
                throw ValidationException::withMessages(['items' => 'Submit every item in this menu exactly once.']);
            }
            foreach ($ids as $position => $id) {
                $menu->items()->whereKey($id)->update(['sort_order' => $position]);
            }
            $this->audit->execute($actor, 'menu.items_reordered', $menu, ['item_count' => count($ids)]);
        });
    }
}
