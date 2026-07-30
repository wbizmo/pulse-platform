<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Content\DeleteTaxonomy;
use App\Actions\Content\SaveTaxonomy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaxonomyRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('admin.tags.index', [
            'tags' => Tag::query()->withCount('posts')->orderBy('name')->paginate(20),
        ]);
    }

    public function store(TaxonomyRequest $request, SaveTaxonomy $save): RedirectResponse
    {
        $save->execute(new Tag, $request->validated(), $request->user());

        return back()->with('success', 'Tag created successfully.');
    }

    public function update(TaxonomyRequest $request, Tag $tag, SaveTaxonomy $save): RedirectResponse
    {
        $save->execute($tag, $request->validated(), $request->user());

        return back()->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag, DeleteTaxonomy $delete): RedirectResponse
    {
        $delete->execute($tag, request()->user());

        return back()->with('success', 'Tag deleted successfully.');
    }
}
