<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\ThemeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeCustomizerController extends Controller
{
    public function index(Theme $theme): View
    {
        $settings = $theme->settings
            ->pluck('value', 'key')
            ->toArray();

        return view('admin.themes.customizer.index', [
            'theme' => $theme,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, Theme $theme): RedirectResponse
    {
        $checkboxes = [
            'show_back_to_top',
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

        $data = $request->validate([
            'logo_url' => ['nullable', 'string'],
            'favicon_url' => ['nullable', 'string'],
            'primary_color' => ['nullable', 'string'],
            'secondary_color' => ['nullable', 'string'],
            'font_family' => ['nullable', 'string'],
            'header_style' => ['nullable', 'string'],
            'footer_style' => ['nullable', 'string'],
            'button_radius' => ['nullable', 'string'],
            'custom_css' => ['nullable', 'string'],
        ]);

        foreach ($data as $key => $value) {
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

        return back()->with('success', 'Theme settings updated successfully.');
    }
}
