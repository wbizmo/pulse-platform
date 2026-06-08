<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('admin.tags.index', [
            'tags' => Tag::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255'],
        ]);

        Tag::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
        ]);

        return back()->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255'],
        ]);

        $tag->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
        ]);

        return back()->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted successfully.');
    }
}
