<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Content\SaveContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::with('author')->latest()->paginate(12)]);
    }

    public function create(): View
    {
        return view('admin.pages.create', ['mediaItems' => $this->mediaChoices()]);
    }

    public function store(PageRequest $request, SaveContent $save): RedirectResponse
    {
        $data = $request->validated() + ['author_id' => $request->user()->id];
        $this->normalizeCheckboxes($data, $request);
        DB::transaction(function () use ($data, $request, $save): void {
            $this->clearPageAssignments($data);
            $save->execute(new Page, $data, $request->user());
        });

        return redirect()->route('admin.pages')->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page, 'mediaItems' => $this->mediaChoices()]);
    }

    public function update(PageRequest $request, Page $page, SaveContent $save): RedirectResponse
    {
        $data = $request->validated();
        $this->normalizeCheckboxes($data, $request);
        DB::transaction(function () use ($data, $request, $page, $save): void {
            $this->clearPageAssignments($data, $page->id);
            $save->execute($page, $data, $request->user());
        });

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page, RecordAudit $audit): RedirectResponse
    {
        DB::transaction(function () use ($page, $audit): void {
            $audit->execute(request()->user(), 'content.deleted', $page, ['status' => $page->status->value]);
            $page->delete();
        });

        return redirect()->route('admin.pages')->with('success', 'Page deleted successfully.');
    }

    private function mediaChoices()
    {
        return Media::query()->where('type', 'image')->latest()->limit(100)->get(['id', 'name', 'path', 'disk']);
    }

    private function normalizeCheckboxes(array &$data, PageRequest $request): void
    {
        foreach (['is_homepage', 'is_blog_page', 'show_header', 'show_footer'] as $field) {
            $data[$field] = $request->boolean($field);
        }
    }

    private function clearPageAssignments(array $data, ?int $id = null): void
    {
        $query = fn () => $id === null ? Page::query() : Page::where('id', '!=', $id);
        if ($data['is_homepage']) {
            $query()->update(['is_homepage' => false]);
        }
        if ($data['is_blog_page']) {
            $query()->update(['is_blog_page' => false]);
        }
    }
}
