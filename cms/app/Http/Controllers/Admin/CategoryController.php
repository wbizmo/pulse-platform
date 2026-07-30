<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Content\DeleteTaxonomy;
use App\Actions\Content\SaveTaxonomy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaxonomyRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('posts')->orderBy('name')->paginate(20),
        ]);
    }

    public function store(
        TaxonomyRequest $request,
        SaveTaxonomy $save
    ): RedirectResponse {
        $save->execute(new Category, $request->validated(), $request->user());

        return back()->with(
            'success',
            'Category created successfully.'
        );
    }

    public function update(
        TaxonomyRequest $request,
        Category $category,
        SaveTaxonomy $save
    ): RedirectResponse {
        $save->execute($category, $request->validated(), $request->user());

        return back()->with(
            'success',
            'Category updated successfully.'
        );
    }

    public function destroy(
        Category $category,
        DeleteTaxonomy $delete
    ): RedirectResponse {
        $delete->execute($category, request()->user());

        return back()->with(
            'success',
            'Category deleted successfully.'
        );
    }
}
