<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Services\Builder\BuilderRenderer;
use App\Services\Seo\SeoResolver;
use App\Services\Themes\ThemeResolver;
use App\Services\Themes\ThemeRuntime;
use Illuminate\View\View;

class FrontendController extends Controller
{
    public function __construct(private readonly SeoResolver $seo, private readonly BuilderRenderer $builder, private readonly ThemeResolver $themes) {}

    public function home(): View
    {
        $runtime = $this->themes->resolve();
        $theme = $runtime->theme;

        $homepageId = $theme
            ? $theme->settings()->where('key', 'homepage_id')->value('value')
            : null;

        $page = $homepageId
            ? Page::with('featuredMedia')->publiclyVisible()->find($homepageId)
            : Page::with('featuredMedia')->publiclyVisible()->where('slug', 'home')->first();

        abort_if(! $page, 404);

        return $this->renderPage($page, $runtime);
    }

    public function blog(): View
    {
        $runtime = $this->themes->resolve();
        $theme = $runtime->theme;

        $data = $this->frontendData($runtime);

        return view('frontend.blog.index', array_merge(
            $data,
            [
                'posts' => Post::with(['category', 'tags', 'author', 'featuredMedia'])
                    ->publiclyVisible()
                    ->latest('published_at')
                    ->paginate(9),
                'seo' => $this->seo->resolve((object) ['title' => 'Blog', 'meta_description' => $data['settings']['site_tagline'] ?? 'Latest posts and updates.'], $data['settings'], route('frontend.blog'), 'archive', request()->integer('page', 1)),
            ]
        ));
    }

    public function post(string $slug): View
    {
        $runtime = $this->themes->resolve();
        $theme = $runtime->theme;

        $post = Post::with(['category', 'tags', 'author', 'featuredMedia'])
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $this->frontendData($runtime);

        return view('frontend.blog.show', array_merge(
            $data,
            [
                'post' => $post, 'seo' => $this->seo->resolve($post, $data['settings'], route('frontend.blog.show', $post->slug), 'post'),
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
        $runtime = $this->themes->resolve();
        $theme = $runtime->theme;
        $posts = $taxonomy->posts()->with(['category', 'tags', 'author', 'featuredMedia'])->publiclyVisible()
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(9);

        $data = $this->frontendData($runtime);
        $canonical = route('frontend.blog.'.strtolower($type), $taxonomy->slug);

        return view('frontend.blog.index', $data + [
            'posts' => $posts,
            'archiveTitle' => $taxonomy->name,
            'archiveDescription' => $taxonomy instanceof Category ? $taxonomy->description : null,
            'archiveCanonical' => $canonical,
            'archiveType' => $type,
            'seo' => $this->seo->resolve((object) ['title' => $taxonomy->name.' '.$type, 'meta_description' => $taxonomy instanceof Category ? $taxonomy->description : null], $data['settings'], $canonical, 'archive', request()->integer('page', 1)),
        ]);
    }

    public function page(string $slug): View
    {
        $page = Page::with('featuredMedia')->publiclyVisible()->where('slug', $slug)->firstOrFail();

        $runtime = $this->themes->resolve();
        $theme = $runtime->theme;

        return $this->renderPage($page, $runtime);
    }

    public function renderPage(Page $page, ThemeRuntime $runtime): View
    {
        $data = $this->frontendData($runtime);

        return view('frontend.page', array_merge(
            $data,
            [
                'page' => $page,
                'builder' => $this->builder->forPage($page),
                'seo' => $this->seo->resolve($page, $data['settings'], $page->is_homepage ? route('frontend.home') : route('frontend.page', $page->slug), $page->is_homepage ? 'site' : 'page'),
            ]
        ));
    }

    public function frontendData(ThemeRuntime $runtime): array
    {
        $settings = Setting::pluck('value', 'key');

        $theme = $runtime->theme;
        $themeSettings = collect($runtime->settings);

        $mainMenu = Menu::publicAt('main');
        $footerMenu = Menu::publicAt('footer');

        return [
            'theme' => $theme,
            'settings' => $settings,
            'themeSettings' => $themeSettings,
            'themeRuntime' => $runtime,
            'mainMenu' => $mainMenu,
            'footerMenu' => $footerMenu,
        ];
    }
}
