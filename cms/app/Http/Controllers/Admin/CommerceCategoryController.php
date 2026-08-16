<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommerceCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommerceCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.commerce.categories', ['categories' => ProductCategory::withCount('products')->orderBy('position')->orderBy('name')->paginate(20)]);
    }

    public function store(CommerceCategoryRequest $r, RecordAudit $audit): RedirectResponse
    {
        $c = ProductCategory::create($r->validated());
        $audit->execute($r->user(), 'commerce.category.created', $c, ['slug' => $c->slug]);

        return back()->with('success', 'Commerce category created.');
    }

    public function update(CommerceCategoryRequest $r, ProductCategory $category, RecordAudit $audit): RedirectResponse
    {
        $category->update($r->validated());
        $audit->execute($r->user(), 'commerce.category.updated', $category, ['slug' => $category->slug]);

        return back()->with('success', 'Commerce category updated.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        abort_unless(request()->user()->can('commerce.products.manage'), 403);
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Assigned categories cannot be deleted.']);
        }$category->delete();

        return back()->with('success', 'Commerce category deleted.');
    }
}
