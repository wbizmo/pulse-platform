<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontendController;
use App\Models\Page;
use App\Models\Post;
use App\Services\Themes\ThemeResolver;
use Illuminate\Http\Response;

class ContentPreviewController extends Controller
{
    public function __construct(private readonly FrontendController $frontend, private readonly ThemeResolver $themes) {}

    public function page(Page $page): Response
    {
        return response($this->frontend->renderPage($page, $this->themes->resolve()))->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function post(Post $post): Response
    {
        $runtime = $this->themes->resolve();

        return response(view('frontend.blog.show', $this->frontend->frontendData($runtime) + ['post' => $post->load(['category', 'tags', 'author'])]))->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
