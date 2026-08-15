<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
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
            ? Page::with('featuredMedia')->publiclyVisible()->find($homepageId)
            : Page::with('featuredMedia')->publiclyVisible()->where('slug', 'home')->first();

        abort_if(! $page, 404);

        return $this->renderPage($page, $theme);
    }

    public function blog(): View
    {
        $theme = Theme::where('is_active', true)->first();

        return view('frontend.blog.index', array_merge(
            $this->frontendData($theme),
            [
                'posts' => Post::with(['category', 'tags', 'author', 'featuredMedia'])
                    ->publiclyVisible()
                    ->latest('published_at')
                    ->paginate(9),
            ]
        ));
    }

    public function post(string $slug): View
    {
        $theme = Theme::where('is_active', true)->first();

        $post = Post::with(['category', 'tags', 'author', 'featuredMedia'])
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

    public function category(string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        return $this->taxonomyArchive($category, 'Category');
    }

    public function tag(string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();

        return $this->taxonomyArchive($tag, 'Tag');
    }

    private function taxonomyArchive(Category|Tag $taxonomy, string $type): View
    {
        $theme = Theme::where('is_active', true)->first();
        $posts = $taxonomy->posts()->with(['category', 'tags', 'author', 'featuredMedia'])->publiclyVisible()
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(9);

        return view('frontend.blog.index', $this->frontendData($theme) + [
            'posts' => $posts,
            'archiveTitle' => $taxonomy->name,
            'archiveDescription' => $taxonomy instanceof Category ? $taxonomy->description : null,
            'archiveCanonical' => route('frontend.blog.'.strtolower($type), $taxonomy->slug),
            'archiveType' => $type,
        ]);
    }

    public function page(string $slug): View
    {
        $page = Page::with('featuredMedia')->publiclyVisible()->where('slug', $slug)->firstOrFail();

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
