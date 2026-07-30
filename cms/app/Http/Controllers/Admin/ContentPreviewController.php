<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontendController;
use App\Models\Page;
use App\Models\Post;
use App\Models\Theme;
use Illuminate\Http\Response;

class ContentPreviewController extends Controller
{
    public function __construct(private readonly FrontendController $frontend) {}

    public function page(Page $page): Response
    {
        return response($this->frontend->renderPage($page, Theme::where('is_active', true)->first()))->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function post(Post $post): Response
    {
        $theme = Theme::where('is_active', true)->first();

        return response(view('frontend.blog.show', $this->frontend->frontendData($theme) + ['post' => $post->load(['category', 'tags', 'author'])]))->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
