<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Content\ReorderMenuItems;
use App\Actions\Content\SaveMenu;
use App\Actions\Content\SaveMenuItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Http\Requests\Admin\MenuRequest;
use App\Http\Requests\Admin\ReorderMenuItemsRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menus.index', ['menus' => Menu::withCount('items')->orderBy('name')->orderBy('id')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.menus.create');
    }

    public function store(MenuRequest $request, SaveMenu $save): RedirectResponse
    {
        $save->execute(new Menu, $request->validated(), $request->user());

        return redirect()->route('admin.menus')->with('success', 'Menu created successfully.');
    }

    public function edit(Request $request, Menu $menu): View
    {
        $pages = Page::query()->when($request->string('page_search')->isNotEmpty(), fn ($q) => $q->where('title', 'like', '%'.addcslashes($request->string('page_search')->toString(), '%_').'%'))->orderBy('title')->limit(50)->get(['id', 'title', 'slug']);

        return view('admin.menus.edit', ['menu' => $menu->load(['items' => fn ($q) => $q->with('page')->orderBy('sort_order')->orderBy('id')]), 'pages' => $pages]);
    }

    public function update(MenuRequest $request, Menu $menu, SaveMenu $save): RedirectResponse
    {
        $save->execute($menu, $request->validated(), $request->user());

        return back()->with('success', 'Menu updated successfully.');
    }

    public function storeItem(MenuItemRequest $request, Menu $menu, SaveMenuItem $save): RedirectResponse
    {
        $save->execute($menu, new MenuItem, $request->validated(), $request->user());

        return back()->with('success', 'Menu item added successfully.');
    }

    public function updateItem(MenuItemRequest $request, Menu $menu, MenuItem $item, SaveMenuItem $save): RedirectResponse
    {
        $this->owned($menu, $item);
        $save->execute($menu, $item, $request->validated(), $request->user());

        return back()->with('success', 'Menu item updated successfully.');
    }

    public function reorder(ReorderMenuItemsRequest $request, Menu $menu, ReorderMenuItems $reorder): RedirectResponse
    {
        $reorder->execute($menu, $request->validated('items'), $request->user());

        return back()->with('success', 'Menu order updated successfully.');
    }

    public function destroyItem(Request $request, Menu $menu, MenuItem $item, RecordAudit $audit): RedirectResponse
    {
        $this->owned($menu, $item);
        DB::transaction(function () use ($request, $menu, $item, $audit): void {
            $audit->execute($request->user(), 'menu.item_deleted', $item, ['menu_id' => $menu->id]);
            $item->delete();
        });

        return back()->with('success', 'Menu item deleted successfully.');
    }

    public function destroy(Request $request, Menu $menu, RecordAudit $audit): RedirectResponse
    {
        DB::transaction(function () use ($request, $menu, $audit): void {
            $audit->execute($request->user(), 'menu.deleted', $menu, ['location' => $menu->location, 'item_count' => $menu->items()->count()]);
            $menu->delete();
        });

        return redirect()->route('admin.menus')->with('success', 'Menu deleted successfully.');
    }

    private function owned(Menu $menu, MenuItem $item): void
    {
        if ($item->menu_id !== $menu->id) {
            throw ValidationException::withMessages(['item' => 'The menu item does not belong to this menu.']);
        }
    }
}
