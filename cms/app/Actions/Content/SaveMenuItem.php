<?php

namespace App\Actions\Content;

use App\Actions\Access\RecordAudit;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveMenuItem
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Menu $menu, MenuItem $item, array $data, User $actor): MenuItem
    {
        return DB::transaction(function () use ($menu, $item, $data, $actor): MenuItem {
            $creating = ! $item->exists;
            if ($creating) {
                $data['sort_order'] = ((int) $menu->items()->lockForUpdate()->max('sort_order')) + 1;
            }
            $data['url'] = $data['type'] === 'page' ? null : $data['url'];
            $item->menu()->associate($menu);
            $item->fill($data)->save();
            $this->audit->execute($actor, $creating ? 'menu.item_created' : 'menu.item_updated', $item, ['menu_id' => $menu->id, 'fields' => array_keys($data)]);

            return $item;
        });
    }
}
