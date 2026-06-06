<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function index(): View
    {
        return view('admin.themes.index', [
            'themes' => Theme::orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'activeTheme' => Theme::where('is_active', true)->first(),
        ]);
    }

    public function activate(Theme $theme): RedirectResponse
    {
        Theme::query()->update([
            'is_active' => false,
        ]);

        $theme->update([
            'is_active' => true,
        ]);

        return back()->with('success', "{$theme->name} is now the active theme.");
    }
}
