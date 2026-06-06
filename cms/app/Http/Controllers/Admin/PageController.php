<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $data['author_id'] = Auth::id();
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        $this->clearPageAssignments($data);

        Page::create($data);

        return redirect()
            ->route('admin.pages')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validatedData($request, $page->id);

        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['published_at'] = $data['status'] === 'published'
            ? ($page->published_at ?? now())
            : null;

        $this->clearPageAssignments($data, $page->id);

        $page->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages')
            ->with('success', 'Page deleted successfully.');
    }

    private function validatedData(Request $request, ?int $pageId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:pages,slug,' . $pageId],
            'status' => ['required', 'in:draft,published'],
            'template' => ['required', 'string', 'max:100'],
            'content' => ['nullable', 'string'],

            'is_homepage' => ['nullable', 'boolean'],
            'is_blog_page' => ['nullable', 'boolean'],
            'show_header' => ['nullable', 'boolean'],
            'show_footer' => ['nullable', 'boolean'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:255'],

            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string', 'max:255'],

            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'string', 'max:255'],
        ]) + [
            'is_homepage' => false,
            'is_blog_page' => false,
            'show_header' => false,
            'show_footer' => false,
        ];
    }

    private function clearPageAssignments(array $data, ?int $currentPageId = null): void
    {
        if ($data['is_homepage']) {
            Page::where('id', '!=', $currentPageId)->update(['is_homepage' => false]);
        }

        if ($data['is_blog_page']) {
            Page::where('id', '!=', $currentPageId)->update(['is_blog_page' => false]);
        }
    }
}
