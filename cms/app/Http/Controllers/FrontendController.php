<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Theme;
use Illuminate\View\View;

class FrontendController extends Controller
{
    public function home(): View
    {
        $theme = Theme::where('is_active', true)->first();

        $homepageId = optional($theme)
            ->settings()
            ->where('key', 'homepage_id')
            ->value('value');

        $page = $homepageId
            ? Page::find($homepageId)
            : Page::where('slug', 'home')->first();

        abort_if(!$page, 404);

        return $this->renderPage($page, $theme);
    }

    public function page(string $slug): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $theme = Theme::where('is_active', true)->first();

        return $this->renderPage($page, $theme);
    }

    protected function renderPage(Page $page, ?Theme $theme): View
    {
        $settings = Setting::pluck('value', 'key');

        $mainMenu = Menu::with('items')
            ->where('location', 'main')
            ->where('is_active', true)
            ->first();

        $footerMenu = Menu::with('items')
            ->where('location', 'footer')
            ->where('is_active', true)
            ->first();

        return view('frontend.page', [
            'page' => $page,
            'theme' => $theme,
            'settings' => $settings,
            'mainMenu' => $mainMenu,
            'footerMenu' => $footerMenu,
        ]);
    }
}
