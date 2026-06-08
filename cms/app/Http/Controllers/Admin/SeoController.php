<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function index(): View
    {
        return view('admin.seo.index', [
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $checkboxes = [
            'seo_sitemap_enabled',
            'seo_robots_enabled',
            'seo_canonical_enabled',
            'seo_noindex_enabled',
            'seo_schema_enabled',
            'seo_social_enabled',
        ];

        foreach ($checkboxes as $checkbox) {
            Setting::updateOrCreate(
                ['key' => $checkbox],
                ['value' => $request->has($checkbox) ? '1' : '0']
            );
        }

        foreach ($request->except(array_merge(['_token'], $checkboxes)) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'SEO settings saved successfully.');
    }
}
