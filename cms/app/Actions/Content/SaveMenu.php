<?php

namespace App\Actions\Content;

use App\Actions\Access\RecordAudit;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveMenu
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Menu $menu, array $data, User $actor): Menu
    {
        return DB::transaction(function () use ($menu, $data, $actor): Menu {
            $data['active_singleton_location'] = $data['is_active'] && in_array($data['location'], ['main', 'footer'], true) ? $data['location'] : null;
            if ($data['is_active'] && in_array($data['location'], ['main', 'footer'], true)) {
                Menu::where('location', $data['location'])->whereKeyNot($menu->getKey())->lockForUpdate()->update(['is_active' => false, 'active_singleton_location' => null]);
            }
            $creating = ! $menu->exists;
            $menu->fill($data)->save();
            $this->audit->execute($actor, $creating ? 'menu.created' : 'menu.updated', $menu, ['location' => $menu->location, 'fields' => array_keys($data)]);

            return $menu;
        });
    }
}
