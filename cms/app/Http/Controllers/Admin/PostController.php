<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Content\SaveContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', ['posts' => Post::with(['category', 'author'])->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.posts.create', $this->taxonomies() + ['mediaItems' => $this->mediaChoices()]);
    }

    public function store(PostRequest $request, SaveContent $save): RedirectResponse
    {
        $data = $request->validated();
        $tags = $data['tags'] ?? null;
        unset($data['tags']);
        $data['user_id'] = $request->user()->id;
        $save->execute(new Post, $data, $request->user(), $tags);

        return redirect()->route('admin.posts')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', ['post' => $post->load('tags'), 'mediaItems' => $this->mediaChoices()] + $this->taxonomies());
    }

    public function update(PostRequest $request, Post $post, SaveContent $save): RedirectResponse
    {
        $data = $request->validated();
        $tags = $data['tags'] ?? null;
        unset($data['tags']);
        $save->execute($post, $data, $request->user(), $tags);

        return back()->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post, RecordAudit $audit): RedirectResponse
    {
        DB::transaction(function () use ($post, $audit): void {
            $audit->execute(request()->user(), 'content.deleted', $post, ['status' => $post->status->value]);
            $post->delete();
        });

        return back()->with('success', 'Post deleted successfully.');
    }

    private function mediaChoices()
    {
        return Media::query()->where('type', 'image')->latest()->limit(100)->get(['id', 'name', 'path', 'disk']);
    }

    private function taxonomies(): array
    {
        return ['categories' => Category::query()->orderBy('name')->limit(500)->get(), 'tags' => Tag::query()->orderBy('name')->limit(500)->get()];
    }
}
