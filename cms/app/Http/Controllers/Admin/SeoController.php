<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Seo\UpdateSeoSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeoSettingsRequest;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function index(): View
    {
        return view('admin.seo.index', [
            'settings' => Setting::pluck('value', 'key'),
            'mediaItems' => Media::query()->where('type', 'image')->latest()->limit(100)->get(['id', 'name']),
        ]);
    }

    public function update(SeoSettingsRequest $request, UpdateSeoSettings $update): RedirectResponse
    {
        $update->execute($request->validated(), $request->user());

        return back()->with('success', 'SEO settings saved successfully.');
    }
}
