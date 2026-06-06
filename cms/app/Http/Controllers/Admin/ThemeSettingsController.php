<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Theme;
use App\Models\ThemeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeSettingsController extends Controller
{
    public function index(Theme $theme): View
    {
        return view('admin.themes.settings.index', [
            'theme' => $theme,
            'settings' => $theme->settings()->pluck('value', 'key'),
            'pages' => Page::orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Theme $theme): RedirectResponse
    {
        $checkboxes = [
            'show_header',
            'show_footer',
            'sticky_header',
            'show_topbar',
            'show_social_links',
            'show_footer_branding',
            'show_newsletter_box',
            'boxed_layout',
        ];

        foreach ($checkboxes as $checkbox) {
            ThemeSetting::updateOrCreate(
                [
                    'theme_id' => $theme->id,
                    'key' => $checkbox,
                ],
                [
                    'value' => $request->has($checkbox) ? '1' : '0',
                ]
            );
        }

        foreach ($request->except(array_merge(['_token'], $checkboxes)) as $key => $value) {
            ThemeSetting::updateOrCreate(
                [
                    'theme_id' => $theme->id,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        return back()->with('success', "{$theme->name} settings saved successfully.");
    }
}
