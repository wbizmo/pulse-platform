<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Builder\SaveBuilder;
use App\Domain\Builder\BlockRegistry;
use App\Domain\Builder\BuilderDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BuilderRequest;
use App\Models\BuilderTemplate;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    public function edit(Page $page, BuilderDocument $documents, BlockRegistry $registry): View
    {
        $document = $documents->empty();
        $legacy = false;
        if ($page->builder_data) {
            try {
                $document = $documents->decode(json_encode($page->builder_data, JSON_THROW_ON_ERROR));
            } catch (\Throwable) {
                $legacy = true;
            }
        }

        return view('admin.builder.edit', [
            'page' => $page,
            'document' => $document,
            'legacyBuilderData' => $legacy,
            'registry' => $registry->editorMetadata(),
            'media' => Media::query()->where('mime_type', 'like', 'image/%')->latest()->limit(100)->get(),
            'templates' => BuilderTemplate::query()->orderBy('name')->limit(100)->get(),
        ]);
    }

    public function update(BuilderRequest $request, Page $page, BuilderDocument $documents, SaveBuilder $save): RedirectResponse
    {
        $document = $documents->decode($request->validated('builder_data'));
        $save->execute($page, $request->user(), $document, (int) $request->validated('lock_version'));

        return back()->with('success', 'Page builder content saved successfully.');
    }
}
