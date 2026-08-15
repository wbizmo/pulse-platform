<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Themes\SaveThemeSettings;
use App\Domain\Themes\ThemeRegistry;
use App\Domain\Themes\ThemeSettings;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Page;
use App\Models\Theme;
use App\Models\ThemeActivation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeCustomizerController extends Controller
{
    public function index(Theme $theme, ThemeRegistry $registry, ThemeSettings $settings): View
    {
        $manifest = $registry->get($theme->slug);

        return view('admin.themes.customizer.index', ['theme' => $theme, 'manifest' => $manifest, 'settings' => $settings->validate($theme->slug, $theme->settings()->pluck('value', 'key')->all()), 'media' => Media::query()->where('type', 'image')->latest('id')->limit(100)->get(), 'pages' => Page::query()->publiclyVisible()->orderBy('title')->limit(100)->get(), 'rollback' => ThemeActivation::query()->whereNotNull('previous_theme_id')->latest('id')->first()]);
    }

    public function update(Request $request, Theme $theme, SaveThemeSettings $save): RedirectResponse
    {
        $save->execute($theme, $request->except('_token'), $request->user());

        return back()->with('success', 'Theme settings saved.');
    }
}
