<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::with([
            'category',
            'author',
        ])
            ->latest()
            ->paginate(20);

        return view('admin.posts.index', [
            'posts' => $posts,
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255'],
            'excerpt' => ['nullable'],
            'content' => ['nullable'],
            'featured_image' => ['nullable'],
            'category_id' => ['nullable'],
            'status' => ['required'],
            'published_at' => ['nullable'],
            'meta_title' => ['nullable'],
            'meta_description' => ['nullable'],
            'og_image' => ['nullable'],
            'tags' => ['nullable', 'array'],
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $data['slug'] ?: Str::slug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'og_image' => $data['og_image'] ?? null,
        ]);

        $post->tags()->sync(
            $data['tags'] ?? []
        );

        return redirect()
            ->route('admin.posts')
            ->with(
                'success',
                'Post created successfully.'
            );
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', [
            'post' => $post->load('tags'),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(
        Request $request,
        Post $post
    ): RedirectResponse {
        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255'],
            'excerpt' => ['nullable'],
            'content' => ['nullable'],
            'featured_image' => ['nullable'],
            'category_id' => ['nullable'],
            'status' => ['required'],
            'published_at' => ['nullable'],
            'meta_title' => ['nullable'],
            'meta_description' => ['nullable'],
            'og_image' => ['nullable'],
            'tags' => ['nullable', 'array'],
        ]);

        $post->update([
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $data['slug'] ?: Str::slug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'og_image' => $data['og_image'] ?? null,
        ]);

        $post->tags()->sync(
            $data['tags'] ?? []
        );

        return back()->with(
            'success',
            'Post updated successfully.'
        );
    }

    public function destroy(
        Post $post
    ): RedirectResponse {
        $post->delete();

        return back()->with(
            'success',
            'Post deleted successfully.'
        );
    }
}
