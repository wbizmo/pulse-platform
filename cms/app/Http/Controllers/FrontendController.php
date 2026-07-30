<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Theme;
use Illuminate\View\View;

class FrontendController extends Controller
{
    public function home(): View
    {
        $theme = Theme::where('is_active', true)->first();

        $homepageId = $theme
            ? $theme->settings()->where('key', 'homepage_id')->value('value')
            : null;

        $page = $homepageId
            ? Page::publiclyVisible()->find($homepageId)
            : Page::publiclyVisible()->where('slug', 'home')->first();

        abort_if(! $page, 404);

        return $this->renderPage($page, $theme);
    }

    public function blog(): View
    {
        $theme = Theme::where('is_active', true)->first();

        return view('frontend.blog.index', array_merge(
            $this->frontendData($theme),
            [
                'posts' => Post::with(['category', 'tags', 'author'])
                    ->publiclyVisible()
                    ->latest('published_at')
                    ->paginate(9),
            ]
        ));
    }

    public function post(string $slug): View
    {
        $theme = Theme::where('is_active', true)->first();

        $post = Post::with(['category', 'tags', 'author'])
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.blog.show', array_merge(
            $this->frontendData($theme),
            [
                'post' => $post,
            ]
        ));
    }

    public function page(string $slug): View
    {
        $page = Page::publiclyVisible()->where('slug', $slug)->firstOrFail();

        $theme = Theme::where('is_active', true)->first();

        return $this->renderPage($page, $theme);
    }

    public function renderPage(Page $page, ?Theme $theme): View
    {
        return view('frontend.page', array_merge(
            $this->frontendData($theme),
            [
                'page' => $page,
            ]
        ));
    }

    public function frontendData(?Theme $theme): array
    {
        $settings = Setting::pluck('value', 'key');

        $themeSettings = $theme
            ? $theme->settings()->pluck('value', 'key')
            : collect();

        $mainMenu = Menu::with('items')
            ->where('location', 'main')
            ->where('is_active', true)
            ->first();

        $footerMenu = Menu::with('items')
            ->where('location', 'footer')
            ->where('is_active', true)
            ->first();

        return [
            'theme' => $theme,
            'settings' => $settings,
            'themeSettings' => $themeSettings,
            'mainMenu' => $mainMenu,
            'footerMenu' => $footerMenu,
        ];
    }
}
