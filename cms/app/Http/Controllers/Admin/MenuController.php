<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menus.index', [
            'menus' => Menu::withCount('items')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateMenu($request);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_active'] = $request->has('is_active');

        Menu::create($data);

        return redirect()
            ->route('admin.menus')
            ->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', [
            'menu' => $menu->load('items.page'),
            'pages' => Page::orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->validateMenu($request, $menu->id);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_active'] = $request->has('is_active');

        $menu->update($data);

        return back()->with('success', 'Menu updated successfully.');
    }

    public function storeItem(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:page,custom'],
            'page_id' => ['nullable', 'exists:pages,id'],
            'url' => ['nullable', 'string', 'max:255'],
            'target' => ['required', 'in:_self,_blank'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['is_active'] = $request->has('is_active');

        if ($data['type'] === 'page') {
            $page = Page::find($data['page_id']);
            $data['url'] = $page ? '/'.$page->slug : null;
        } else {
            $data['page_id'] = null;
        }

        $menu->items()->create($data);

        return back()->with('success', 'Menu item added successfully.');
    }

    public function destroyItem(MenuItem $item): RedirectResponse
    {
        $item->delete();

        return back()->with('success', 'Menu item deleted successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()
            ->route('admin.menus')
            ->with('success', 'Menu deleted successfully.');
    }

    private function validateMenu(Request $request, ?int $menuId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:menus,slug,'.$menuId],
            'location' => ['required', 'in:main,footer,legal,sidebar,custom'],
        ]);
    }
}
