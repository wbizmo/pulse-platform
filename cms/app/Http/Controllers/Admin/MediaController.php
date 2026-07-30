<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Media::latest();

        if ($request->filled('search')) {
            $query->where(function ($mediaQuery) use ($request) {
                $mediaQuery
                    ->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('original_name', 'like', '%'.$request->search.'%')
                    ->orWhere('mime_type', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return view('admin.media.index', [
            'mediaItems' => $query->paginate(18)->withQueryString(),
            'activeType' => $request->type,
            'search' => $request->search,
        ]);
    }

    public function library(Request $request): JsonResponse
    {
        $mediaItems = Media::query()
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->latest()
            ->take(60)
            ->get()
            ->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'original_name' => $media->original_name,
                'url' => url($media->url),
                'type' => $media->type,
                'mime_type' => $media->mime_type,
                'size' => $media->readable_size,
            ]);

        return response()->json([
            'media' => $mediaItems,
        ]);
    }

    public function upload(): View
    {
        return view('admin.media.upload');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file', 'max:20480'],
        ]);

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $fileName = Str::uuid().'.'.$extension;
            $path = $file->storeAs('media', $fileName, 'public');

            Media::create([
                'user_id' => Auth::id(),
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'disk' => 'public',
                'path' => $path,
                'url' => Storage::url($path),
                'size' => $file->getSize(),
                'type' => $this->detectType($mimeType),
            ]);
        }

        return redirect()
            ->route('admin.media')
            ->with('success', 'Media uploaded successfully.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->path);

        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }

    private function detectType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_contains($mimeType, 'pdf')) {
            return 'document';
        }

        return 'file';
    }
}
