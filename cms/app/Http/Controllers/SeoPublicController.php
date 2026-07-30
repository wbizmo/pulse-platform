<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Response;

class SeoPublicController extends Controller
{
    public function sitemap(): Response
    {
        $settings = Setting::pluck('value', 'key');

        if (($settings['seo_sitemap_enabled'] ?? '1') !== '1') {
            abort(404);
        }

        $pages = Page::query()
            ->where('status', 'published')
            ->orderBy('updated_at', 'desc')
            ->get();

        $posts = Post::query()
            ->where('status', 'published')
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = view('seo.sitemap', [
            'pages' => $pages,
            'posts' => $posts,
        ])->render();

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $settings = Setting::pluck('value', 'key');

        if (($settings['seo_robots_enabled'] ?? '1') !== '1') {
            abort(404);
        }

        $content = $settings['seo_robots_content']
            ?? "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml');

        $content = str_replace('/sitemap.xml', url('/sitemap.xml'), $content);

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
