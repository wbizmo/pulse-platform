<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Media\DeleteMedia;
use App\Actions\Media\StoreMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', 'in:image']]);
        $query = Media::query()->with('user:id,name')->withCount(['pages', 'posts'])->latest('id');
        if (! empty($validated['search'])) {
            $term = addcslashes($validated['search'], '%_\\');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('original_name', 'like', "%{$term}%"));
        }
        if (! empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        return view('admin.media.index', ['mediaItems' => $query->paginate(18)->withQueryString(), 'activeType' => $validated['type'] ?? null, 'search' => $validated['search'] ?? null]);
    }

    public function library(Request $request): JsonResponse
    {
        $items = Media::query()->where('type', 'image')->latest('id')->paginate(30)->through(fn (Media $media) => ['id' => $media->id, 'name' => $media->name, 'url' => $media->public_url, 'mime_type' => $media->mime_type, 'size' => $media->readable_size]);

        return response()->json($items);
    }

    public function upload(): View
    {
        return view('admin.media.upload');
    }

    public function store(StoreMediaRequest $request, StoreMedia $store): RedirectResponse
    {
        foreach ($request->file('files') as $file) {
            $store->execute($file, $request->user());
        }

        return redirect()->route('admin.media')->with('success', 'Media uploaded successfully.');
    }

    public function update(UpdateMediaRequest $request, Media $media, RecordAudit $audit): RedirectResponse
    {
        $media->update($request->validated());
        $audit->execute($request->user(), 'media.updated', $media, ['fields' => array_keys($request->validated())]);

        return back()->with('success', 'Media details updated.');
    }

    public function destroy(Request $request, Media $media, DeleteMedia $delete): RedirectResponse
    {
        $delete->execute($media, $request->user());

        return back()->with('success', 'Media deleted successfully.');
    }
}
