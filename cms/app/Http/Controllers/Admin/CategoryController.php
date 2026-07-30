<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::latest()->get(),
        ]);
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255'],
            'description' => ['nullable'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => $data['slug']
                ?: Str::slug($data['name']),
            'description' => $data['description']
                ?? null,
        ]);

        return back()->with(
            'success',
            'Category created successfully.'
        );
    }

    public function update(
        Request $request,
        Category $category
    ): RedirectResponse {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255'],
            'description' => ['nullable'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => $data['slug']
                ?: Str::slug($data['name']),
            'description' => $data['description']
                ?? null,
        ]);

        return back()->with(
            'success',
            'Category updated successfully.'
        );
    }

    public function destroy(
        Category $category
    ): RedirectResponse {
        $category->delete();

        return back()->with(
            'success',
            'Category deleted successfully.'
        );
    }
}
