<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Themes\ActivateTheme;
use App\Domain\Themes\ThemeRegistry;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontendController;
use App\Models\Page;
use App\Models\Theme;
use App\Models\ThemeActivation;
use App\Services\Themes\ThemeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function index(ThemeRegistry $registry): View
    {
        return view('admin.themes.index', ['themes' => Theme::query()->whereNull('retired_at')->whereIn('slug', array_keys($registry->all()))->orderByDesc('is_active')->get(), 'activeTheme' => Theme::query()->whereNotNull('active_slot')->first(), 'history' => ThemeActivation::query()->latest('id')->limit(20)->get()]);
    }

    public function activate(Theme $theme, ActivateTheme $activate): RedirectResponse
    {
        $activate->execute($theme, request()->user());

        return back()->with('success', "{$theme->name} is now active.");
    }

    public function rollback(ThemeActivation $activation, ActivateTheme $activate): RedirectResponse
    {
        abort_unless($activation->previous_theme_id, 422);
        $activate->execute(Theme::findOrFail($activation->previous_theme_id), request()->user(), $activation);

        return back()->with('success', 'The prior theme and its settings were restored.');
    }

    public function preview(Theme $theme, Page $page, ThemeResolver $resolver, FrontendController $frontend): Response
    {
        $runtime = $resolver->resolve($theme);

        return response($frontend->renderPage($page, $runtime))->header('Cache-Control', 'private, no-store, max-age=0')->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
